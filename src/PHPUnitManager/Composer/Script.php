<?php
namespace PHPUnitManager\Composer;

class Script
{   
    protected array $scripts = [];
    
    public function __construct(array $scripts)
    {
       $this->scripts = $scripts;        
    }
    public function execute($scriptName) 
    {
         if (!array_key_exists($scriptName, $this->scripts)) {
            echo "Erro: O script '{$scriptName}' não foi encontrado no composer.json.\n";
            echo "Dica: Rode 'php test-manager.php show' para ver os nomes disponíveis.\n";
           exit(1);
        }
    
    echo "Executando: composer {$scriptName}\n\n";
    passthru("composer {$scriptName}", $exitCode);
    exit($exitCode);

    }
}

