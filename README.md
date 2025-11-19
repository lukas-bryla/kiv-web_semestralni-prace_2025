# Dokumentace aplikace EventHub

## Základní informace

- **Autor:** Lukáš Brýla
- **E‑mail:** lbryla@students.zcu.cz
- **Datum vytvoření:** 18. 11. 2025
- **Předmět:** KIV/WEB – Webové technologie
- **Název aplikace:** EventHub – správa akcí a registrací

## URL aplikace

Předpokládá se spuštění v Dockeru podle `docker-compose.yml`, potom:

- Veřejná část:
  - `http://localhost:8000/` – domovská stránka
  - `http://localhost:8000/events` – seznam akcí s filtrováním
  - `http://localhost:8000/event/{id}` – detail konkrétní akce
  - `http://localhost:8000/event/create` – vytvoření nové akce (jen přihlášený uživatel)
  - `http://localhost:8000/my-events` – „Moje akce“ – akce, na které je uživatel registrován
  - `http://localhost:8000/login` – přihlášení
  - `http://localhost:8000/register` – registrace
  - `http://localhost:8000/logout` – odhlášení
  - `http://localhost:8000/terms` – podmínky použití
  - `http://localhost:8000/privacy` – ochrana soukromí
  - `http://localhost:8000/api/events` – JSON API se seznamem akcí
- Administrace:
  - `http://localhost:8000/admin` – administrátorský panel (admin + superadmin)
  - `http://localhost:8000/moderator` – moderátorský panel (moderator + admin + superadmin)
- Další:
  - `http://localhost:8000/sse/free-spots.php?event_id={id}` – SSE stream volných míst na akci

## Popis aplikace (zadání)

EventHub je webová aplikace pro správu událostí, workshopů a konferencí. Umožňuje:

- návštěvníkům prohlížet seznam nadcházejících akcí, filtrovat je podle textu, tagů a data,
- zobrazit detail akce včetně dostupné kapacity v reálném čase,
- registrovaným uživatelům:
  - vytvářet nové akce (které čekají na schválení),
  - registrovat se na akce (pokud není plná kapacita),
  - sledovat přehled „Moje akce“,
  - psát komentáře k akcím (po schválení moderátorem),
- moderátorům:
  - schvalovat/odmítat nové akce,
  - schvalovat/skrývat komentáře k akcím,
  - vidět seznam registrovaných účastníků na detailu akce,
- administrátorům:
  - vše výše jako moderátor,
  - spravovat uživatele (přidělování rolí user/moderator/admin, mazání),
- superadminovi:
  - je speciální administrátor (uživatel s `id = 1`), jediný smí spravovat ostatní administrátory
    (měnit jejich role a mazat je), ale sám sebe nemůže změnit ani smazat.

Aplikace obsahuje i jednoduchý newsletter (sběr e‑mailů v patičce) a statické stránky
s podmínkami a ochranou soukromí.

## Použité technologie

- **PHP 8+**
  - Backend aplikace, vlastní mini MVC framework.
  - Implementace routeru, controllerů, modelů a služeb (`src/`).
- **Twig**
  - Šablonovací systém pro generování HTML.
  - Všechny HTML stránky jsou v adresáři `templates/`.
- **MySQL 8**
  - Relační databáze pro uživatele, akce, registrace, komentáře, newsletter a tagy.
  - Schéma definováno v `database/install.sql`.
- **Docker & docker-compose**
  - `php-fpm` + `nginx` pro PHP aplikaci.
  - `mysql` databáze s automatickým importem `install.sql`.
  - `adminer` pro pohodlnou správu databáze.
- **Bootstrap 5 + Bootstrap Icons**
  - Základní responzivní layout a komponenty (navbar, karty, tabulky, tlačítka, alerty).
  - Custom styly v `public/assets/css/app.css`.
- **CKEditor 5**
  - Rich-text editor pro popis akce v `templates/event/create.twig`.
- **JavaScript (SSE – Server-Sent Events)**
  - `public/sse/free-spots.php` poskytuje stream volných míst.
  - Event detail (`templates/event/show.twig`) dynamicky aktualizuje počet volných míst.

## Adresářová struktura

- `public/`
  - `index.php` – vstupní bod aplikace (front controller).
  - `sse/free-spots.php` – SSE endpoint pro volná místa.
  - `assets/css/app.css` – vlastní CSS pro aplikaci.
  - `assets/images/` – (případné) obrázky/placeholdery.
  - `uploads/` – nahrané obrázky plakátů akcí.
- `src/`
  - `Core/`
    - `App.php` – inicializace Twig, routeru, definice rout, spuštění aplikace.
    - `Router.php` – jednoduchý router (mapování URL na controller@metodu).
    - `BaseController.php` – base třída controllerů (redirect, flash, role check).
    - `Database.php` – singleton wrapper pro PDO připojení k MySQL.
  - `Controller/`
    - `HomeController.php` – domovská stránka.
    - `AuthController.php` – login, registrace, odhlášení.
    - `EventController.php` – seznam, filtr, detail, vytvoření akce, „Moje akce“.
    - `AdminController.php` – administrace uživatelů a schvalování akcí.
    - `ModeratorController.php` – moderace akcí a komentářů.
    - `NewsletterController.php` – ukládání e‑mailů do newsletteru.
    - `CommentController.php` – odeslání komentáře k akci.
    - `RegistrationController.php` – registrace na akci.
    - `StaticController.php` – statické stránky (terms, privacy).
    - `ApiController.php` – JSON API se seznamem akcí.
  - `Model/`
    - `UserModel.php` – práce s uživateli (CRUD, role).
    - `EventModel.php` – práce s akcemi, filtrech a tagy (JOIN na `tags`/`event_tag`).
    - `RegistrationModel.php` – registrace na akce a přehled akcí/účastníků.
    - `CommentModel.php` – ukládání a načítání komentářů dle stavu.
  - `Services/`
    - `UploadService.php` – nahrávání souborů (obrázků akcí) s kontrolou typu/velikosti.
- `templates/`
  - `base.twig` – základní layout, vkládá navbar, flash a footer.
  - `partials/` – částečné šablony:
    - `navbar.twig` – horní navigace (Domů, Akce, Moje akce, Moderátor, Admin, login/registrovat).
    - `footer.twig` – patička, odkazy, newsletter.
    - `flash.twig` – zobrazení flash zpráv.
  - `home/index.twig` – domovská stránka (hero sekce, call-to-action).
  - `event/index.twig` – seznam akcí s filtrem a tagy.
  - `event/create.twig` – formulář pro vytvoření akce.
  - `event/show.twig` – detail akce, registrace, komentáře, seznam účastníků (pro role).
  - `event/my-events.twig` – seznam akcí, na které je uživatel registrován.
  - `auth/login.twig`, `auth/register.twig` – přihlášení a registrace.
  - `admin/index.twig` – administrace uživatelů a čekajících akcí.
  - `moderator/index.twig` – moderace akcí a komentářů.
  - `static/terms.twig`, `static/privacy.twig` – statické informace.
  - `errors/404.twig` – stránka „nenalezeno“.
- `database/install.sql`
  - Kompletní SQL skript pro vytvoření databáze, tabulek a testovacích dat.
- `docker/`
  - `Dockerfile` – build PHP-FPM kontejneru.
  - `nginx/default.conf` – konfigurace nginx virtuálního hosta.
- `config/config.php`
  - Konfigurace `BASE_URL` a cesty pro nahrané soubory.
- `vendor/`
  - Composer závislosti (Twig aj.).

## Architektura aplikace

Aplikace používá jednoduchou architekturu ve stylu MVC:

- **Router (`App\Core\Router`)**
  - Udržuje seznam rout (HTTP metoda + regex cesta + controller@metoda).
  - V `App::defineRoutes()` jsou registrovány všechny routy.
- **App (`App\Core\App`)**
  - Inicializuje Twig, globální proměnné (session, flash).
  - Nastavuje router a definuje routy.
  - Implementuje jednoduchý middleware: pro URL začínající `admin`, `moderator` nebo `/event/create` a `/my-events` vyžaduje přihlášení.
  - Volá `Router::dispatch()`.
- **BaseController**
  - `redirect($url)` – HTTP redirect na `/$url`.
  - `flash($type, $message)` – uloží flash zprávu do session.
  - `isRole($requiredRole)` – kontrola role dle hierarchie
    (`user < moderator < admin < superadmin`).
- **Modely**
  - `UserModel`
    - Registrace, vyhledání uživatele, seznam uživatelů.
    - `updateRole` a `delete` obsahují ochranu pro superadmina (`id = 1`).
  - `EventModel`
    - Čtení seznamu akcí s filtry (text, tagy, budoucí).
    - Vytvoření akce + uložení vazeb na tagy (`event_tag`).
    - Načtení detailu akce (včetně autora a seznamu tagů).
  - `RegistrationModel`
    - Ověření registrace uživatele na akci.
    - Registrace na akci s kontrolou kapacity.
    - Seznam akcí, na které je uživatel registrován.
    - Seznam účastníků pro konkrétní akci.
  - `CommentModel`
    - Uložení nového komentáře jako „pending“.
    - Načítání schválených komentářů k akci.
    - Výpis čekajících komentářů pro moderátory a změna stavu (approved/rejected).
- **Controllery**
  - `HomeController` – pouze renderuje `home/index.twig`.
  - `AuthController` – login, registrace, logout.
  - `EventController`
    - `index` – seznam akcí, filtry (q, tags[], upcoming).
    - `create`/`store` – vytvoření nové akce (pouze role ≥ user), nahrání obrázku.
    - `show` – detail akce, komentáře, registrace, seznam účastníků.
    - `myEvents` – „Moje akce“ pro přihlášeného uživatele.
  - `AdminController`
    - Chrání vstup `isRole('admin')` – admin i superadmin.
    - Schvalování/odmítání akcí.
    - Správa uživatelů (role, mazání) se speciální logikou:
      - superadmin (id=1) nelze měnit ani mazat,
      - pouze superadmin může měnit role adminů a mazat adminy.
  - `ModeratorController`
    - `index` – přehled čekajících akcí a komentářů.
    - `approveEvent`/`rejectEvent` – schvalování/mazání akcí.
    - `approveComment`/`rejectComment` – schvalování/skrývání komentářů.
  - `CommentController`
    - `store` – odeslání komentáře (pouze přihlášený uživatel) – uloží se jako čekající.
  - `RegistrationController`
    - `register` – registrace na akci:
      - kontrola přihlášení,
      - ověření, že akce existuje a je schválená,
      - kontrola duplicity a kapacity.
  - `NewsletterController`
    - Uložení e‑mailu do `newsletter_emails` (INSERT IGNORE).
  - `StaticController`
    - Render statických šablon `terms.twig` a `privacy.twig`.
  - `ApiController`
    - JSON výpis akcí (vhodné např. pro frontend nebo integrace).

## Defaultní uživatelé

Podle `database/install.sql`:

- **superadmin**
  - username: `superadmin`
  - e-mail: `superadmin@eventhub.cz`
  - role: `superadmin`
  - heslo (plaintext): `password`
- **admin**
  - username: `admin`
  - e-mail: `admin@eventhub.cz`
  - role: `admin`
  - heslo: `password`
- **moderator**
  - username: `moderator`
  - e-mail: `mod@eventhub.cz`
  - role: `moderator`
  - heslo: `password`
- **běžní uživatelé**
  - username: `pepa`, e-mail: `pepa@example.cz`, role: `user`, heslo: `password`
  - username: `jana`, e-mail: `jana@example.cz`, role: `user`, heslo: `password`

> Pozn.: V produkci by bylo vhodné hesla změnit a zakázat zveřejňování jejich plaintext hodnot.

## Spuštění v Dockeru – kompletní guide

Předpoklady:

- nainstalovaný Docker

Postup:

1. Naklonujte repozitář / zkopírujte projekt do adresáře:

   ```bash
   git clone https://github.com/lukas-bryla/kiv-web_semestralni-prace_2025 eventhub
   cd eventhub
   ```

2. Spusťte kontejnery:

   ```bash
   docker compose up -d
   ```

   Vytvoří se tyto služby:

   - `php` – PHP-FPM kontejner (build z `docker/Dockerfile`), spouští Composer instalaci.
   - `nginx` – webserver, mapuje `localhost:8000` na PHP aplikaci.
   - `mysql` – databáze MySQL 8 (init skriptem `database/install.sql`).
   - `adminer` – DB GUI na `localhost:8080`.

3. Po naběhnutí MySQL se automaticky provede `install.sql`, takže DB je připravena.

4. Aplikaci otevřete v prohlížeči:

   - `http://localhost:8000` – frontend.
   - `http://localhost:8080` – Adminer (DB):
     - server: `mysql`
     - user: `eventhub`
     - password: `eventhub123`
     - database: `eventhub`

5. Přihlášení do aplikace:

   - Přihlášení jako superadmin: `superadmin` / `password`.
   - Přihlášení jako admin: `admin` / `password`.
   - Přihlášení jako moderator: `moderator` / `password`.

6. Zastavení:

   ```bash
   docker compose down
   ```

   Pro smazání DB (volitelně):

   ```bash
   docker compose down -v
   ```

## Kompletní popis funkcí aplikace

### Role a oprávnění

- **guest (nepřihlášený)**
  - Může prohlížet seznam akcí, filtrování, detail akce (bez registrace na akci).
  - Může číst schválené komentáře.
  - Může se přihlásit k newsletteru a otevřít statické stránky.
- **user**
  - Vše jako guest.
  - Může se registrovat na akce (pokud není plná kapacita).
  - Může vytvářet nové akce (čekají na schválení).
  - Může psát komentáře k akcím (čekají na schválení moderátorem).
  - Může vidět „Moje akce“ (na které je registrován).
- **moderator**
  - Vše jako user.
  - Přístup na `/moderator` – moderátorský panel.
  - Může schvalovat/odmítat akce.
  - Může schvalovat/skrývat komentáře.
  - Na detailu akce vidí seznam registrovaných účastníků (jméno, e‑mail, čas registrace).
- **admin**
  - Vše jako moderator.
  - Přístup na `/admin` – admin panel.
  - Může schvalovat/odmítat akce.
  - Může spravovat uživatele (s omezeními viz níže).
- **superadmin**
  - Vše jako admin.
  - Speciální pravidla:
    - jediný může měnit role adminů,
    - jediný může mazat uživatele v roli admin,
    - nelze měnit ani mazat sám sebe (id=1).

### Seznam akcí /events

- Zobrazuje všechny schválené akce (`approved = 1`), seřazené podle data.
- Pro každou akci:
  - název, datum a čas, místo, případně obrázek,
  - tagy (odvozené z `tags` přes `event_tag`),
  - tlačítko „Detail“.
  - je-li přihlášený uživatel registrovaný na tuto akci:
    - badge „Registrováno“.
- Filtrování:
  - **Hledat (q)** – fulltext v názvu, místě, popisu a názvech tagů.
  - **Tagy (tags[])** – multi-select přes checkboxy/popular-badges:
    - akce musí obsahovat všechny vybrané tagy (AND filtr).
    - kliknutím na aktivní tag v „Tagy:“ se tag z filtru odebere (toggle).
  - **Jen budoucí akce** – zobrazí pouze akce s datem >= aktuální čas.
- Filtry jsou navzájem kombinovatelné (text + více tagů + budoucí).

### Detail akce /event/{id}

- Zobrazuje:
  - obrázek (nebo placeholder),
  - název, autora, datum, místo,
  - tagy (badge),
  - kapacitu a volná místa v reálném čase.
- **Volná místa**:
  - přes SSE (EventSource) se periodicky načítá
    `capacity - COUNT(registrations)` pro danou akci.
  - při chybě SSE se vypíše „N/A (chyba spojení)“.
- **Registrace na akci**:
  - pokud je uživatel přihlášený:
    - a už je registrován → badge „Jsi registrován na tuto akci“.
    - jinak vidí tlačítko „Registrovat se“ (`POST /event/{id}/register`).
  - controller ověří:
    - kapacitu akce,
    - zda registrace ještě neexistuje.
  - při úspěchu flash hláška „Úspěšně jsi se registroval na akci.“
- **Komentáře**:
  - viditelné jsou pouze schválené komentáře (`status = 1`).
  - zobrazují se ve formě seznamu: autor, čas, text.
  - přihlášený uživatel může odeslat komentář:
    - formulář `POST /event/{id}/comment`,
    - komentář se uloží se stavem „pending“ a zobrazí se až po schválení moderátorem.
- **Seznam účastníků** (jen pro moderator/admin/superadmin):
  - zobrazí se sekce „Registrovaní účastníci“:
    - pro každého: username, e‑mail a čas registrace.
  - pokud nikdo není registrován, zobrazí se informace o prázdném seznamu.

### Vytvoření akce /event/create

- Jen pro přihlášené uživatele (role ≥ `user`).
- Formulář:
  - název, popis (CKEditor), datum/čas, kapacita, místo, obrázek (volitelný),
  - tagy (hardcoded set tagů z DB) – výběr přes checkboxy (může vybrat více tagů).
- Po odeslání:
  - aplikace validuje povinná pole a formát data.
  - nahraje soubor (pokud je poslán) – kontrola typu a velikosti.
  - uloží akci se stavem `approved = 0` (čeká na schválení).
  - při úspěchu flash „Akce byla úspěšně odeslána ke schválení!“ a přesměrování na `/events`.

### Moje akce /my-events

- Jen pro přihlášené uživatele.
- Zobrazuje karty všech akcí, na které je uživatel registrován (jen schválené).
- U každé akce:
  - stejný vzhled jako `/events`,
  - badge `Registrováno`,
  - tlačítko „Detail akce“.

### Moderátorský panel /moderator

- Přístup pro role `moderator`, `admin`, `superadmin`.
- Sekce „Čekající akce ke schválení“:
  - seznam akcí s `approved = 0`,
  - pro každou akci:
    - tlačítko „Schválit“ (`POST /moderator/event/approve/{id}`),
    - tlačítko „Odmítnout“ (`POST /moderator/event/reject/{id}`) – akce se smaže.
- Sekce „Čekající komentáře“:
  - vypisuje komentáře se stavem `pending`,
  - u každého komentáře:
    - uživatel, název akce, čas, text,
    - tlačítko „Schválit“ → `status = approved`,
    - tlačítko „Skrýt“ → `status = rejected`,
    - odkaz na detail akce.

### Admin panel /admin

- Přístup pro `admin` a `superadmin`.
- Část „Čekající akce na schválení“ – stejná logika jako u moderátora.
- Část „Uživatelé“:
  - tabulka všech uživatelů (`id, username, email, role, created_at`).
  - pro superadmina:
    - plná správa rolí (kromě superadmina) a mazání uživatelů.
  - pro admina:
    - může měnit role pouze user/moderator,
    - nemůže měnit roli adminů,
    - nemůže nikoho povýšit na admina,
    - nemůže mazat adminy.
  - superadmin (id=1) má speciální řádek:
    - text „SuperAdmin – nelze měnit“, „Bez akcí“.

### Autentizace

- **Registrace (/register)**
  - vyžaduje username, e‑mail, heslo, heslo znovu.
  - heslo se hashuje přes bcrypt.
  - nově registrovaný uživatel má roli `user`.
- **Přihlášení (/login)**
  - ověření username a hesla oproti DB.
  - při úspěchu se do session uloží `user` (id, username, role).
- **Odhlášení (/logout)**
  - vyčištění session a přesměrování na homepage.

### Newsletter

- Formulář v patičce:
  - POST na `/newsletter` s e‑mailem.
  - `NewsletterController`:
    - kontrola vyplnění e‑mailu a základní validace formátu.
    - `INSERT IGNORE` do `newsletter_emails` – duplicitní e‑mail nevyvolá chybu.
    - flash zpráva o úspěchu/ chybě.

### Statické stránky /terms a /privacy

- Renderují Twig šablony s textem podmínek a zásad ochrany soukromí.

### API /api/events

- Vrací JSON seznam akcí (např. pro externí použití nebo budoucí SPA frontend).
- Lze rozšířit o parametry pro stejné filtrování jako `/events`.
