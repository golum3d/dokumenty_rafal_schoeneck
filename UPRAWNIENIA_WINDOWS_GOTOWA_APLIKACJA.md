# Gotowa aplikacja na Windows IIS - wymagane uprawnienia

Ta wersja jest dla sytuacji, w której cały katalog aplikacji jest już skopiowany na serwer i trzeba tylko poprawnie ustawić IIS, PHP 8.5 i uprawnienia do folderów.

## 1. Założenie

Lokalizacja aplikacji:

```text
C:\inetpub\zarzadzenia
```

Katalog witryny w IIS:

```text
C:\inetpub\zarzadzenia\public
```

## 2. Konto, które musi dostać uprawnienia

Uprawnienia nadaj kontu puli aplikacji IIS:

```text
IIS AppPool\zarzadzenia
```

## 3. Gdzie nadać uprawnienia

To konto musi mieć uprawnienia do zapisu do:

```text
C:\inetpub\zarzadzenia\storage
C:\inetpub\zarzadzenia\bootstrap\cache
```

W praktyce najważniejsze są te katalogi:

```text
C:\inetpub\zarzadzenia\storage\app\private
C:\inetpub\zarzadzenia\storage\framework\cache
C:\inetpub\zarzadzenia\storage\framework\sessions
C:\inetpub\zarzadzenia\storage\framework\views
C:\inetpub\zarzadzenia\storage\logs
C:\inetpub\zarzadzenia\bootstrap\cache
```

## 4. Jakie uprawnienia nadać

Nadaj temu kontu:

- `Modify`
- `Read & Execute`
- `List folder contents`
- `Read`
- `Write`

Najprościej:

- na `storage` nadaj `Modify` z dziedziczeniem na podfoldery i pliki
- na `bootstrap\cache` nadaj `Modify` z dziedziczeniem na podfoldery i pliki

Nie trzeba dawać `Full Control`.

## 5. Jak nadać uprawnienia w GUI

1. Kliknij prawym na folder `C:\inetpub\zarzadzenia\storage`
2. Wybierz `Properties`
3. Otwórz zakładkę `Security`
4. Kliknij `Edit`
5. Kliknij `Add`
6. Wpisz nazwę konta:

```text
IIS AppPool\zarzadzenia
```

7. Kliknij `Check Names`
8. Kliknij `OK`
9. Zaznacz `Modify`, wtedy Windows automatycznie zaznaczy też niższe prawa
10. Zatwierdź zmiany
11. Powtórz to samo dla `C:\inetpub\zarzadzenia\bootstrap\cache`

Jeżeli pojawi się opcja dziedziczenia, zostaw włączone dziedziczenie na podfoldery i pliki.

## 6. Jak nadać uprawnienia w PowerShell

Jeżeli administrator woli konsolę, można użyć:

```powershell
icacls "C:\inetpub\zarzadzenia\storage" /grant "IIS AppPool\zarzadzenia:(OI)(CI)M" /T
icacls "C:\inetpub\zarzadzenia\bootstrap\cache" /grant "IIS AppPool\zarzadzenia:(OI)(CI)M" /T
```

Znaczenie:

- `(OI)` dziedziczenie na pliki
- `(CI)` dziedziczenie na foldery
- `M` oznacza `Modify`

## 7. Co jeszcze sprawdzić po nadaniu praw

1. Czy w IIS witryna wskazuje na:

```text
C:\inetpub\zarzadzenia\public
```

2. Czy w `public\web.config` jest poprawna ścieżka do:

```text
php-cgi.exe
```

3. Czy używana pula aplikacji to dokładnie ta, której nadałeś prawa.

## 8. Szybki zestaw poleceń po ustawieniu uprawnień

Uruchom z właściwym PHP 8.5:

```powershell
cd C:\inetpub\zarzadzenia
C:\PHP\8.5\php.exe artisan optimize:clear
C:\PHP\8.5\php.exe artisan config:cache
C:\PHP\8.5\php.exe artisan route:cache
C:\PHP\8.5\php.exe artisan view:cache
```

Jeżeli baza jeszcze nie była przygotowana:

```powershell
C:\PHP\8.5\php.exe artisan migrate --force
```

## 9. Jak rozpoznać, że uprawnienia są złe

Najczęstsze objawy:

- błąd `500`
- brak możliwości logowania
- brak zapisu sesji
- brak uploadu PDF
- błędy w `storage\logs\laravel.log`
- komunikaty o braku dostępu do `storage`, `cache`, `views`, `sessions` albo `logs`

## 10. Minimalna wersja dla administratora

Jeżeli chcesz przekazać adminowi tylko najkrótszą instrukcję, wystarczy:

1. Ustaw site root na `C:\inetpub\zarzadzenia\public`
2. Ustaw pulę aplikacji `zarzadzenia`
3. Nadaj `IIS AppPool\zarzadzenia` prawo `Modify` do:

```text
C:\inetpub\zarzadzenia\storage
C:\inetpub\zarzadzenia\bootstrap\cache
```

4. Sprawdź ścieżkę do PHP 8.5 w `public\web.config`
5. Wykonaj:

```powershell
cd C:\inetpub\zarzadzenia
C:\PHP\8.5\php.exe artisan optimize:clear
C:\PHP\8.5\php.exe artisan config:cache
```

To jest minimalny zestaw, żeby aplikacja mogła działać poprawnie po skopiowaniu na serwer.
