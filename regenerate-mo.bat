echo "Regenerando traducciones DB-SafeTrigger..."

cd languages

echo "Regenerando en_US..."
if exist db-safetrigger-en_US.po (
    echo msgfmt db-safetrigger-en_US.po -o db-safetrigger-en_US.mo
    if exist db-safetrigger-en_US.mo (
        echo "✅ en_US generado"
    ) else (
        echo "❌ Error generando en_US"
    )
) else (
    echo "❌ No encontrado: db-safetrigger-en_US.po"
)

echo.
echo "Regenerando es_ES..."
if exist db-safetrigger-es_ES.po (
    echo msgfmt db-safetrigger-es_ES.po -o db-safetrigger-es_ES.mo
    if exist db-safetrigger-es_ES.mo (
        echo "✅ es_ES generado"
    ) else (
        echo "❌ Error generando es_ES"
    )
) else (
    echo "❌ No encontrado: db-safetrigger-es_ES.po"
)

cd ..
echo.
echo "Para aplicar cambios: refresca WordPress o reactiva el plugin"
pause