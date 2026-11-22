<?php
namespace PHPUnitManager\Handle;

use PHPUnitManager\Composer\Script;

class Run extends AbstractHandle
{
    
    /**
     * Executa testes individuais, por grupo ou todos.
     */
    public function run(): void
    {
        $composer = new Script($this->scripts);
        
        $mode   = '-n'; // Default: executar por nome
        $target = null;
        
        foreach ($this->params as $param) {
            
            if (in_array($param, ['-g', '-n', '-a'], true)) {
                $mode = $param;
            } elseif ($target === null) {
                $target = $param;
            }
        }
        
        if ($mode === '-a') {
            
            $composer->execute('all-tests');
            
            return;
        }
        
        if (empty($target)) {
            
            echo "Erro: Informe o nome do teste ou grupo.\n";
            echo "Uso: php test-manager.php run [-g | -n | -a] <nome>\n";
            exit(1);
        }
        
        if ($mode === '-g') {
            // Permite passar "unit" ou "unit-tests"
            $groupKey = str_ends_with($target, '-tests') ? $target : "{$target}-tests";
            
            $composer->execute($groupKey);
            
            return;
        }
        
        // Modo -n (default): busca pelo prefixo "test:"
        $testKey = str_starts_with($target, 'test:') ? $target : "test:{$target}";
        
        $composer->execute($testKey);
    }
}

