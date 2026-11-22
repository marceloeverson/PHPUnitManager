<?php

declare(strict_types=1);

namespace PHPUnitManager;

use Closure;

class Manager
{
    
const HELP_STRING="

Gerenciador de Testes PHPUnit

Uso:
  php test-manager.php <comando> [opções] [argumentos]
  
Comandos:
  help                            Mostra todas as opções disponíveis.
  
  run [-g | -n | -a] <nome>       Executa os testes registrados:
                                    -n <nome>   Roda um teste específico pelo nome sem 'test:' (padrão).
                                    -g <grupo>  Roda todos os testes de um grupo (ex: unit ou unit-tests).
                                    -a          Roda todos os testes (ignora o <nome> se informado).
                                    
  show [-g]                       Lista os testes e seus respectivos grupos (exceto all-tests):
                                    -g          Mostra apenas os grupos disponíveis. 

  sync                            Sincroniza todos os testes no composer.json                            

";
const HELP_OPTIONS=['help','--help','-h'];
private string $composerFile="";
private array $args;
private array $strategies = [];
/**
 * @param string $composerFile
 * @param array $args
 */
public function __construct(string $composerFile,array $args=[]) 
{
    $this->composerFile = $composerFile;
    $this->args = array_slice($args, 1);
}
public function dispatch()
{
    
    if (!file_exists($this->composerFile)) {
        exit("Erro: Arquivo {$this->composerFile} não encontrado.\n");
    }    
     
    $this->handleStrategy($this->args[0]?? '');
    
}
public function registerStrategy(string $strategy, callable $callback): void
{
   $this->strategies[$strategy] = $callback;
}
private function handleStrategy(string $name) 
{
    
    $handleClass = "PHPUnitManager\\Handle\\".ucfirst($name);
    
    if(isset($this->strategies[$name]) && $this->strategies[$name] instanceof Closure) {
        $handle = Closure::bind($this->strategies[$name], $this);
        call_user_func($handle,$this->args);
    }
    else if(class_exists($handleClass)){ 
        
        $composerData = json_decode(file_get_contents($this->composerFile), true);
        $scripts      = $composerData['scripts'] ?? [];  
        $handle       = new $handleClass(array_slice($this->args, 1),$scripts);
        $handle->run();
        
    }
    else if (in_array(strtolower($name), self::HELP_OPTIONS)){        
        $this->showHelp();        
    }
    else{        
        $this->showHelp($name);
    }
    
  }
  public function showHelp(?string $invalidCommand = null): void
  {
      if ($invalidCommand !== null) {
          echo "Comando desconhecido: '{$invalidCommand}'\n\n";
      }      
      echo trim(self::HELP_STRING).PHP_EOL;
      exit($invalidCommand !== null ? 1 : 0);
  }
  
}