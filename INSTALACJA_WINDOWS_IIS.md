# Instalacja aplikacji na Windows Server + IIS 10 + PHP 8.5

To jest instrukcja wdrożenia tej aplikacji Laravel 13 na serwerze produkcyjnym z IIS 10 i PHP 8.5.

## 1. Co wdrażamy

Aplikacja:

- działa na `Laravel 13`
- wymaga `PHP >= 8.3`, więc `PHP 8.5` jest poprawne
- korzysta z bazy danych
- przechowuje przesłane pliki PDF w `storage/app/private/documents`
- używa assetów budowanych przez `Vite` do katalogu `public/build`
- używa sesji i cache domyślnie w bazie danych
- ma domyślnie skonfigurowaną kolejkę `database`, ale w aktualnym kodzie nie ma zadań kolejkowanych, więc worker nie jest wymagany do uruchomienia aplikacji

## 2. Wymagania po stronie serwera

Na serwerze muszą być zainstalowane:

- `IIS 10`
- `URL Rewrite` dla IIS
- `PHP 8.5` w trybie `FastCGI`
- `Composer 2`
- baza danych: `MySQL/MariaDB`, `SQL Server` albo `PostgreSQL`

Rozszerzenia PHP wymagane lub zalecane:

- `ctype`
- `curl`
- `dom`
- `fileinfo`
- `filter`
- `hash`
- `intl`
- `json`
- `mbstring`
- `openssl`
- `pdo`
- sterownik bazy danych:
  - `pdo_mysql` dla MySQL/MariaDB
  - `sqlsrv` i `pdo_sqlsrv` dla SQL Server
  - `pdo_pgsql` dla PostgreSQL
- `session`
- `tokenizer`
- `xml`
- `zip`

## 3. Zalecana struktura katalogów

Przykład:

```text
C:\www\dokumenty\
```

Do tego katalogu kopiujemy cały projekt.

Ważne:

- strona IIS ma wskazywać na katalog `public`, nie na root projektu
- przykładowo fizyczna ścieżka witryny powinna być ustawiona na:

```text
C:\www\dokumenty\public
```

Przy wielu wersjach PHP na jednym serwerze nie używaj ogólnego `php` z `PATH`, tylko pełnej ścieżki do właściwego interpretera, na przykład:

```powershell
C:\PHP\8.5\php.exe -v
```

## 4. Przygotowanie artefaktu wdrożeniowego

Najbezpieczniej jest budować aplikację poza serwerem produkcyjnym i na serwer wrzucać już gotowy artefakt.

Minimalny pakiet do wdrożenia powinien zawierać:

- cały kod aplikacji
- katalog `vendor`
- katalog `public/build`
- plik `artisan`
- katalogi `bootstrap`, `config`, `database`, `resources`, `routes`, `storage`

Jeśli nie budujesz artefaktu wcześniej, możesz wykonać instalację Composer i build assetów bezpośrednio na serwerze.

## 5. Kopiowanie plików na serwer

1. Skopiuj projekt do:

```powershell
C:\www\dokumenty
```

2. Upewnij się, że w projekcie istnieją katalogi:

```text
storage\app\private
storage\framework\cache\data
storage\framework\sessions
storage\framework\views
bootstrap\cache
```

3. Jeśli wdrażasz bez katalogu `vendor`, uruchom:

```powershell
cd C:\www\dokumenty
C:\PHP\8.5\php.exe C:\ProgramData\ComposerSetup\bin\composer.phar install --no-dev --optimize-autoloader
```

4. Jeśli wdrażasz bez katalogu `public/build`, uruchom:

```powershell
npm ci
npm run build
```

Jeżeli na produkcji nie ma Node.js, build wykonaj wcześniej poza serwerem i skopiuj gotowy `public/build`.

## 6. Utworzenie pliku .env

Ten projekt obecnie nie zawiera pliku `.env.example`, więc plik `.env` trzeba utworzyć ręcznie.

W katalogu:

```text
C:\www\dokumenty\.env
```

utwórz plik podobny do poniższego:

```dotenv
APP_NAME="Dokumenty"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://twoja-domena.pl

APP_LOCALE=pl
APP_FALLBACK_LOCALE=pl
APP_FAKER_LOCALE=pl_PL

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dokumenty
DB_USERNAME=dokumenty_user
DB_PASSWORD=TU_WPISZ_HASLO

CACHE_STORE=database
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database

SESSION_SECURE_COOKIE=true
```

Jeżeli używasz SQL Server, zmień na przykład:

```dotenv
DB_CONNECTION=sqlsrv
DB_HOST=NAZWA_SERWERA_LUB_IP
DB_PORT=1433
DB_DATABASE=dokumenty
DB_USERNAME=dokumenty_user
DB_PASSWORD=TU_WPISZ_HASLO
```

## 7. Generowanie klucza aplikacji

Po utworzeniu `.env` uruchom:

```powershell
cd C:\www\dokumenty
C:\PHP\8.5\php.exe artisan key:generate
```

Po tej operacji w `.env` pojawi się `APP_KEY`.

## 8. Przygotowanie bazy danych

1. Utwórz pustą bazę danych.
2. Nadaj użytkownikowi prawa do tworzenia i modyfikacji tabel.
3. Uruchom migracje:

```powershell
cd C:\www\dokumenty
C:\PHP\8.5\php.exe artisan migrate --force
```

To polecenie utworzy między innymi:

- `users`
- `sessions`
- `cache`
- `jobs`
- `documents`
- `document_histories`
- `document_categories`
- `document_statuses`
- `folders`

## 9. Utworzenie pierwszego użytkownika administratora

Seeder w repo tworzy tylko testowego użytkownika i nie nadaje gotowego hasła produkcyjnego, więc konto administratora najlepiej utworzyć ręcznie.

Uruchom:

```powershell
cd C:\www\dokumenty
C:\PHP\8.5\php.exe artisan tinker
```

Następnie w konsoli Tinker:

```php
\App\Models\User::create([
    'name' => 'admin',
    'email' => 'admin@twoja-domena.pl',
    'password' => 'SilneHaslo123!',
    'roles' => ['admin', 'pracownik_merytoryczny'],
]);
```

Wyjdź z Tinker poleceniem:

```php
exit
```

## 10. Uprawnienia NTFS

Konto puli aplikacji IIS musi mieć zapis do:

- `C:\www\dokumenty\storage`
- `C:\www\dokumenty\bootstrap\cache`

Najczęściej będzie to konto:

```text
IIS AppPool\<nazwa_puli_aplikacji>
```

Nadaj temu kontu uprawnienia:

- `Modify`
- `Read & Execute`
- `List folder contents`
- `Read`
- `Write`

Bez tego aplikacja nie zapisze:

- sesji
- cache
- logów
- przesłanych dokumentów PDF

## 11. Konfiguracja IIS

### 11.1. Application Pool

Utwórz osobny `Application Pool`, np.:

```text
dokumenty_pool
```

Ustawienia:

- `.NET CLR version`: `No Managed Code`
- `Managed pipeline mode`: `Integrated`
- `Load User Profile`: `True`
- `Start Mode`: `AlwaysRunning` opcjonalnie

### 11.2. Witryna

Utwórz nową witrynę w IIS:

- `Site name`: `dokumenty`
- `Physical path`: `C:\www\dokumenty\public`
- przypisz wcześniej utworzoną pulę aplikacji
- skonfiguruj bindingi `http/https`

### 11.3. PHP 8.5 jako jedna z wielu wersji PHP

Ponieważ na serwerze jest wiele wersji PHP, ta witryna powinna być przypięta konkretnie do `php-cgi.exe` od PHP 8.5.

W repo dodałem plik:

- [public/web.config](/Applications/ServBay/www/www_dokumenty/public/web.config)

Ten plik:

- ustawia `index.php` jako dokument domyślny
- dodaje rewrite dla Laravel
- przypina obsługę `*.php` do:

```text
C:\PHP\8.5\php-cgi.exe
```

Jeśli na Twoim serwerze PHP 8.5 jest w innej lokalizacji, zmień w `public/web.config` wartość:

```xml
scriptProcessor="C:\PHP\8.5\php-cgi.exe"
```

na właściwą ścieżkę.

## 12. Cache aplikacji

Po wdrożeniu uruchom:

```powershell
cd C:\www\dokumenty
C:\PHP\8.5\php.exe artisan config:clear
C:\PHP\8.5\php.exe artisan cache:clear
C:\PHP\8.5\php.exe artisan route:clear
C:\PHP\8.5\php.exe artisan view:clear
C:\PHP\8.5\php.exe artisan config:cache
C:\PHP\8.5\php.exe artisan route:cache
C:\PHP\8.5\php.exe artisan view:cache
```

## 13. Storage i pliki PDF

Ta aplikacja zapisuje dokumenty PDF do prywatnego storage:

```text
storage/app/private/documents
```

Pliki nie są wystawiane bezpośrednio przez IIS, tylko przez kontroler Laravel, co jest poprawne dla dokumentów wewnętrznych.

`php artisan storage:link` nie jest wymagane do działania uploadów PDF w aktualnej wersji aplikacji.

## 14. Harmonogram i kolejka

W aktualnym kodzie nie znalazłem zadań harmonogramu ani jobów, które byłyby realnie dispatchowane.

W praktyce:

- scheduler nie jest obecnie wymagany
- worker kolejki nie jest obecnie wymagany do podstawowego działania

Jeżeli w przyszłości pojawią się joby, wtedy trzeba będzie dodać osobny proces, np. jako usługę Windows:

```powershell
C:\PHP\8.5\php.exe artisan queue:work --queue=default --sleep=3 --tries=3
```

## 15. Test powdrożeniowy

Po wdrożeniu sprawdź:

1. Czy otwiera się ekran logowania.
2. Czy logowanie działa dla utworzonego administratora.
3. Czy można wejść do sekcji `Użytkownicy`, `Kategorie`, `Statusy`.
4. Czy można dodać dokument PDF.
5. Czy działa podgląd dokumentu.
6. Czy działa pobieranie dokumentu.
7. Czy w `storage\logs\laravel.log` nie pojawiają się błędy uprawnień.

## 16. Najczęstsze problemy

### 404 dla wszystkich tras poza `/`

Najczęściej:

- brak `URL Rewrite`
- witryna wskazuje na zły katalog
- site root ustawiony na `C:\www\dokumenty` zamiast `C:\www\dokumenty\public`

### 500 po wejściu na stronę

Najczęściej:

- błędny `.env`
- brak `APP_KEY`
- brak rozszerzenia PHP
- brak uprawnień zapisu do `storage` lub `bootstrap\cache`

### Brak stylów / JS

Najczęściej:

- nie został wykonany `npm run build`
- nie został wdrożony katalog `public/build`

### Nie działa logowanie

Najczęściej:

- migracje nie zostały wykonane
- tabele `sessions` lub `users` nie istnieją
- ciasteczka HTTPS są włączone, a witryna działa tylko po HTTP

Jeżeli środowisko działa tylko po HTTP, tymczasowo ustaw:

```dotenv
SESSION_SECURE_COOKIE=false
```

Docelowo produkcja powinna działać po HTTPS.

## 17. Minimalna sekwencja poleceń

Jeśli wszystko robisz bezpośrednio na serwerze, skrócona sekwencja wygląda tak:

```powershell
cd C:\www\dokumenty
C:\PHP\8.5\php.exe C:\ProgramData\ComposerSetup\bin\composer.phar install --no-dev --optimize-autoloader
npm ci
npm run build
C:\PHP\8.5\php.exe artisan key:generate
C:\PHP\8.5\php.exe artisan migrate --force
C:\PHP\8.5\php.exe artisan config:cache
C:\PHP\8.5\php.exe artisan route:cache
C:\PHP\8.5\php.exe artisan view:cache
```

## 18. Rekomendacja dla tej aplikacji

Dla tej aplikacji rekomenduję:

- baza `MySQL` albo `SQL Server`
- build assetów poza produkcją
- osobny `Application Pool`
- site root ustawiony na `public`
- prywatne PDF wyłącznie w `storage/app/private`
- HTTPS od pierwszego uruchomienia
