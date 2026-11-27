# Imagem para rodar o template Filament como PHP REAL (php-fpm + nginx).
# Base serversideup (Laravel-ready) + as extensões PHP que o Filament usa e que
# a imagem base não traz por padrão:
#   - intl   -> obrigatório p/ ->money()/Number (NumberFormatter); sem ele as
#               páginas com colunas monetárias (Produtos/Pedidos) dão 500
#   - bcmath -> aritmética de precisão (somatórios/sumarizadores)
#   - gd / exif -> processamento de imagens (FileUpload de avatar/imagem)
FROM serversideup/php:8.4-fpm-nginx

USER root
RUN install-php-extensions intl bcmath gd exif
USER www-data
