# deifickle.com

The blog is plain Markdown files. A small PHP script (`index.php`) turns them into web pages when someone visits, so there is no generator to install and nothing to build.

## Writing a post

1. Create a file in `posts/` named `YYYY-MM-DD-short-name.md`, for example `posts/2026-10-01-shaders.md`.
2. Start it with this header, then write the post in Markdown below it:

   ```
   ---
   title: "Shaders"
   date: 2026-10-01T10:00:00+05:30
   description: One line shown in the post list
   tags: [CPP, graphics]
   ---
   ```

3. Commit it to the `release` branch (see Publishing). It appears on the home page, in its tags and in the RSS feed straight away.

- The post's address is `/posts/<file name in lowercase>/`, e.g. `/posts/2026-10-01-shaders/`.
- Add `draft: true` to the header to keep a post hidden.
- To link to another post, use its address: `[previous post](/posts/2023-01-22-virtualfunc1/)`.
- Images go in `res/` and are used as `![alt text](/res/picture.png)`.

## Pages

Every `.md` file in the top folder (except this README) is a page at `/<file name>/`, e.g. `about.md` is `/about/`. The header links are set in `config.php`.

## Publishing on NearlyFreeSpeech.net

The site needs a site type that runs PHP (in the NFSN member panel, the site's "Server Type" should be a PHP option, not "Static Content").

Normally you don't upload anything: every change to the `release` branch is copied to the site by the GitHub Action in `.github/workflows/deploy.yml`. It mirrors the repository into `/home/public`, so a file removed here is removed from the site too. You can also start it by hand from the repository's Actions tab ("Deploy to NFSN", then "Run workflow").

`.htaccess` hides `lib/` and `config.php` from visitors.

### Automatic deploy: one-time setup

1. On your computer, create a key used only for deploying:
   `ssh-keygen -t ed25519 -f nfsn_deploy -N "" -C "github-deploy"`
   This makes `nfsn_deploy` (private) and `nfsn_deploy.pub` (public).
2. In the NFSN member panel, open **Profile**, choose **Add SSH Key** and paste the contents of `nfsn_deploy.pub`.
3. On the site's page in the member panel, note the **SSH/SFTP hostname** (for example `ssh.nyc1.nearlyfreespeech.net`) and **username** (for example `yourlogin_sitename`).
4. Run `ssh-keyscan <hostname>` and keep the output. It lets the Action check it is talking to the real NFSN server.
5. In GitHub, open the repository's **Settings > Secrets and variables > Actions** and add four repository secrets:
   - `NFSN_HOST`: the hostname from step 3
   - `NFSN_USER`: the username from step 3
   - `NFSN_SSH_KEY`: the whole contents of `nfsn_deploy` (the private file)
   - `NFSN_KNOWN_HOSTS`: the output of step 4
6. Run the workflow once from the Actions tab and check the site. Then delete `nfsn_deploy` from your computer, or keep it somewhere safe.

Until all four secrets exist, the Action runs but skips the copy.

## Testing on your own computer

With PHP installed: `php -S localhost:8000 index.php` in this folder, then open http://localhost:8000.

## Files

| File | What it does |
|---|---|
| `config.php` | Site title, address, header and footer links |
| `index.php` | Decides which page to show for each address |
| `lib/site.php` | Reads the Markdown files, builds the post list, tags and RSS feed |
| `lib/layout.php` | The HTML around every page |
| `lib/Parsedown.php` | Markdown library ([Parsedown](https://github.com/erusev/parsedown), MIT licence) |
| `style.css` | Look of the site, with automatic dark mode |
| `.htaccess` | Sends every address to `index.php` |
| `.github/workflows/deploy.yml` | Copies the site to NFSN when `release` changes |
