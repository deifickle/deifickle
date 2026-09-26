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

3. Upload the file (see Publishing). It appears on the home page, in its tags and in the RSS feed straight away.

- The post's address is `/posts/<file name in lowercase>/`, e.g. `/posts/2026-10-01-shaders/`.
- Add `draft: true` to the header to keep a post hidden.
- To link to another post, use its address: `[previous post](/posts/2023-01-22-virtualfunc1/)`.
- Images go in `res/` and are used as `![alt text](/res/picture.png)`.

## Pages

Every `.md` file in the top folder (except this README) is a page at `/<file name>/`, e.g. `about.md` is `/about/`. The header links are set in `config.php`.

## Publishing on NearlyFreeSpeech.net

The site needs a site type that runs PHP (in the NFSN member panel, the site's "Server Type" should be a PHP option, not "Static Content").

Either:

- **SFTP:** upload the whole folder to `/home/public`, and afterwards only the files you changed; or
- **git over SSH:** once, `git clone https://github.com/deifickle/deifickle.git /home/public` (the folder must be empty first). After that, publishing is `cd /home/public && git pull`.

`.htaccess` hides the `.git` folder, `lib/` and `config.php` from visitors.

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
