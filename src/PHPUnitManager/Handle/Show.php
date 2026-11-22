<?php
namespace PHPUnitManager\Handle;

class Show extends AbstractHandle
{
       
    public function run(): void
    {
        $onlyGroups = in_array('-g', $this->params, true);
        
        // Mapeia grupos (exceto 'all-tests') e testes
        $groups = [];
        $tests  = [];
        
        foreach ($this->scripts as $key => $value) {
            if ($key === 'all-tests') {
                continue;
            }
            
            if (str_ends_with($key, '-tests') && is_array($value)) {
                $groups[$key] = $value;
            } elseif (str_starts_with($key, 'test:')) {
                $tests[] = $key;
            }
        }
        
        if (empty($groups) && empty($tests)) {
            echo "Nenhum teste ou grupo registrado no composer.json.\n";
            exit(0);
        }
        
        if ($onlyGroups) {
            echo "Grupos disponíveis:\n";
            if (empty($groups)) {
                echo "  (Nenhum grupo encontrado)\n";
                exit(0);
            }
            
            foreach ($groups as $groupName => $items) {
                $shortGroup = str_replace('-tests', '', $groupName);
                $count = count($items);
                echo "  - {$shortGroup} ({$groupName}) [{$count} teste(s)]\n";
            }
            exit(0);
        }
        
        // Cria mapa reverso: @test:nome => [grupo1, grupo2]
        $testToGroups = [];
        foreach ($groups as $groupName => $refs) {
            $shortGroup = str_replace('-tests', '', $groupName);
            foreach ($refs as $ref) {
                $cleanRef = ltrim($ref, '@');
                $testToGroups[$cleanRef][] = $shortGroup;
            }
        }
        
        echo "Testes registrados:\n";
        foreach ($tests as $testKey) {
            $shortName = str_replace('test:', '', $testKey);
            $assignedGroups = $testToGroups[$testKey] ?? ['sem grupo'];
            $groupsLabel = implode(', ', $assignedGroups);
            
            echo "  - {$shortName}  [Grupo: {$groupsLabel}]\n";
        }
    }
}

