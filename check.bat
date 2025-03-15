@echo off
echo Running PHP CS Fixer...
set PHP_CS_FIXER_IGNORE_ENV=1
vendor\bin\php-cs-fixer fix 
if errorlevel 1 (
    echo PHP CS Fixer found issues
    exit /b 1
)

echo.
echo Running PHP_CodeSniffer...
vendor\bin\phpcs --standard=phpcs.xml
if errorlevel 1 (
    echo PHP_CodeSniffer found issues
    exit /b 1
)

echo.
echo Running PHPStan...
vendor\bin\phpstan analyse
if errorlevel 1 (
    echo PHPStan found issues
    exit /b 1
)

echo.
echo Running PHPUnit...
vendor\bin\phpunit
if errorlevel 1 (
    echo PHPUnit tests failed
    exit /b 1
)

echo.
echo All checks passed successfully! 