<?php

declare(strict_types=1);

namespace PHPUnitManager;

use RecursiveDirectoryIterator;

class Sync
{

private string $composerFile="";
private string $composerFilePath="";
private string $testsLockFile ="";
private string $testsDir= "";
private string $PHPUnitBin="vendor/bin/phpunit";
private bool $showWarnings=false;
 
public function __construct(string $composerFile,string $testsPath,?string $PHPUnitBin=null,bool $showWarnings=false) 
{
    $this->composerFilePath  = dirname($composerFile);
    $this->composerFile      = $composerFile;
    $this->testsLockFile     = $this->composerFilePath. '/.phpunit-tests.lock';
    $this->testsDir          = $testsPath;
    $this->PHPUnitBin        = $PHPUnitBin??"vendor/bin/phpunit";
    $this->showWarnings      = $showWarnings;
}
public function run()
{

if (!is_dir($this->testsDir)) {
    exit("Erro: Pasta {$this->testsDir} não encontrada.\n");
}
if (!file_exists($this->composerFile)) {
    exit("Erro: Arquivo {$this->composerFile} não encontrado.\n");
}

$iterator = new \RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator($this->testsDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$discoveredScripts = [];
$groupedTests = [];
$allTestReferences = [];

foreach ($iterator as $file) {
    
    if ($file->isFile() && str_ends_with($file->getFilename(), 'Test.php')) {
        // Caminho relativo a partir da raiz do projeto (ex: tests/Unit/AuthTest.php)
        $relativePath = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $file->getPathname());
        $relativePath = str_replace('\\', '/', $relativePath);

        // Caminho relativo dentro da pasta tests/ (ex: Unit/AuthTest.php)
        $insideTestsPath = ltrim(str_replace($this->testsDir, '', $file->getPathname()), '/\\');
        $pathParts = explode(DIRECTORY_SEPARATOR, $insideTestsPath);

        // Gera a tag individual (ex: UserAuthTest.php -> test:user-auth)
        $baseName  = str_replace('Test.php', '', $file->getFilename());
        $kebabName = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $baseName));
        $tag       = "test:{$kebabName}";
        $reference = "@{$tag}";

        $warnings = $this->showWarnings?"--display-warnings":"";
        $discoveredScripts[$tag] = sprintf("%s %s %s",$this->PHPUnitBin ,$warnings, $relativePath);
        $allTestReferences[]     = $reference;

        // Se o teste estiver dentro de uma subpasta (ex: tests/Unit/...), agrupa
        if (count($pathParts) > 1) {
            $subFolder = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $pathParts[0]));
            $groupKey = "{$subFolder}-tests";
            $groupedTests[$groupKey][] = $reference;
        }
    }
  }

    ksort($discoveredScripts);
    sort($allTestReferences);
    ksort($groupedTests);

    foreach ($groupedTests as &$refs) {
        sort($refs);
    }
    unset($refs);

    // 2. Verifica se houve adição, remoção ou mudança de pasta comparando com o cache
    $currentState = [
        'scripts' => $discoveredScripts,
        'groups' => $groupedTests,
    ];

    $previousState = file_exists($this->testsLockFile)
        ? json_decode(file_get_contents($this->testsLockFile), true)
        : [];

    if ($currentState === $previousState) {
        echo "Nenhuma alteração nos testes detectada.\n";
        exit(0);
    }

    echo "Alterações detectadas nos testes! Atualizando composer.json...\n";

    // 3. Atualiza o composer.json mantendo scripts personalizados existentes
    $composerData = json_decode(file_get_contents($this->composerFile), true);
    $composerData['scripts'] = $composerData['scripts'] ?? [];

    $olderAllTests           =  $composerData['scripts']['all-tests']?? [];
    $outerTestsInAllTests    =  array_diff($olderAllTests,$allTestReferences);
    $allTests                =  array_merge($outerTestsInAllTests,$allTestReferences);
    sort($allTests);

    // Remove tags individuais antigas ("test:*") e grupos antigos ("*-tests")
    foreach (array_keys($composerData['scripts']) as $key) {
        if (str_starts_with($key, 'test:') || str_ends_with($key, '-tests')) {
            unset($composerData['scripts'][$key]);
        }
    }

    // Mescla scripts individuais, grupos por subpasta e o grupo geral 'all-tests'
    $composerData['scripts'] = array_merge(
        $composerData['scripts'],
        $discoveredScripts,
        $groupedTests,
        ['all-tests' => $allTests]
    );

    file_put_contents(
        $this->composerFile,
        json_encode($composerData, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES) . PHP_EOL
    );

    // Atualiza o arquivo de controle
    file_put_contents(
        $this->testsLockFile,
        json_encode($currentState, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES)
    );

    // 4. Roda o composer update
    echo "Executando composer update...\n";
    passthru('composer update', $exitCode);

    if ($exitCode === 0) {
        echo "Sincronização concluída com sucesso!\n";
    } else {
        echo "Erro ao executar composer update.\n";
    }

}


}