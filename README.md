
# Instalação do módulo VirtusPay Magento 2


### Baixar módulo
[link do packgist](https://packagist.org/packages/virtuspay/magento2-payment)

Na raiz do projeto executar o comando:

```shell
composer require virtuspay/magento2-payment
```

### Iniciar a configuração do módulo na loja
```shell
bin/magento setup:upgrade
```


### Compilar o projeto/loja novamente
```shell
bin/magento setup:di:compile
```


### Configurar o pagamento
Na tela administrativa do magento, seguir o caminho:
```
Stores > configuration > Sales > Payment Methods > others
```

### VirtusPay Pagamentos
- **Ativar** `Yes/No` 
*Ativa ou desativa módulo*

- **Ambiente** `Produção/Homologação`
*Seleciona entre fazer transação em produção ou em ambiente de desenvolvimento(Homologação)*

- **Token** `id do merchant`
*Token *
