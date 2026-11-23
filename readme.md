# 🧪 PHPUnitManager

> Um gerenciador de linha de comando (CLI) em PHP para organizar, sincronizar e executar suítes de testes do PHPUnit diretamente através dos scripts do `composer.json`.

![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![Composer](https://img.shields.io/badge/composer-%23885630.svg?style=for-the-badge&logo=composer&logoColor=white)


---

## 📋 Sobre o Projeto

O **PHPUnitManager** facilita a rotina de testes em projetos PHP ao automatizar o registro de arquivos de teste dentro do `composer.json` e oferecer uma interface de linha de comando simples e intuitiva para rodar testes individuais, grupos específicos ou toda a suíte de testes sem precisar decorar caminhos longos de diretórios.

---

## ✨ Funcionalidades

- **Sincronização Automática (`sync`):** Varre o diretório de testes e registra os scripts automaticamente no seu `composer.json`.
- **Execução Flexível (`run`):** Permite rodar um teste específico por nome, filtrar por grupo (ex: `unit`, `integration`) ou executar todos os testes de uma só vez.
- **Listagem Organizada (`show`):** Exibe todos os testes cadastrados e seus respectivos grupos diretamente no terminal.
- **Extensível via Estratégias:** Arquitetura baseada em estratégias (`registerStrategy`), permitindo adicionar ou customizar comandos facilmente.

---

## ⚙️ Pré-requisitos

* **PHP** 8.0 ou superior
* **Composer** instalado
* **PHPUnit** configurado no projeto

---

## 🚀 Instalação e Configuração

### 1. Instale via Composer (ou clone o repositório)

```bash
git clone https://github.com/marceloeverson/PHPUnitManager.git
```

### 2. Crie o script gerenciador na raiz do seu projeto

Crie um arquivo chamado `test-manager.php` na raiz do seu projeto (ao lado do `composer.json`) com o seguinte conteúdo:

```php
<?php

use PHPUnitManager\Manager;
use PHPUnitManager\Sync;

include __DIR__ . "/vendor/autoload.php";

$composerFile = __DIR__ . "/composer.json";
$testsPath    = __DIR__ . "/tests/items"; // Diretório onde ficam seus arquivos de teste

$manager = new Manager(composerFile, argv);

// Registra o comando de sincronização
$manager->registerStrategy('sync', function() use ($composerFile, $testsPath) {
    $sync = new Sync($composerFile, $testsPath, null, true)
    $sync->run();
});

$manager->dispatch();
```

---

## 💻 Como Usar (CLI)

Execute o script através do terminal utilizando a sintaxe:

```bash
php test-manager.php <comando> [opções] [argumentos]
```

### Comandos Disponíveis

| Comando | Opções | Descrição |
| :--- | :--- | :--- |
| `help` | | Mostra todas as opções e instruções disponíveis. |
| `sync` | | Sincroniza todos os arquivos de teste no `composer.json`. |
| `show` | `[-g]` | Lista os testes registrados e seus grupos (exceto `all-tests`). Use `-g` para listar apenas os grupos. |
| `run` | `[-n \| -g \| -a] <nome>` | Executa os testes registrados conforme a flag escolhida. |

### Detalhes das opções do comando `run`

* **`-n <nome>`** *(Padrão)*: Roda um teste específico pelo nome (sem o prefixo `test:`).
* **`-g <grupo>`**: Roda todos os testes pertencentes a um grupo (ex: `unit` ou `unit-tests`).
* **`-a`**: Roda todos os testes registrados (ignora o `<nome>` caso seja informado).

### Exemplos Práticos

```bash
# 1. Sincronizar novos arquivos de teste com o composer.json
php test-manager.php sync

# 2. Listar todos os testes e grupos disponíveis
php test-manager.php show

# 3. Listar apenas os grupos de testes
php test-manager.php show -g

# 4. Executar um teste específico pelo nome
php test-manager.php run -n UserTest
# ou simplesmente (já que -n é o padrão):
php test-manager.php run UserTest

# 5. Executar todos os testes de um grupo específico
php test-manager.php run -g unit

# 6. Executar toda a suíte de testes
php test-manager.php run -a
```

---

## 🤝 Como Contribuir

Contribuições são muito bem-vindas!

1. Faça um **Fork** do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Faça o commit das suas alterações (`git commit -m 'Adiciona MinhaFeature'`)
4. Faça o push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um **Pull Request**

---

## 👤 Autor

Desenvolvido por **[Marcelo Everson](https://github.com/marceloeverson)**.
