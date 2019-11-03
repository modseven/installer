<p align="center"><b>Installer for Mod(ern)(Ko)seven Framework</b></p>

<p align="center">
  <a href="https://packagist.org/packages/modseven/installer"><img src="https://poser.pugx.org/modseven/installer/license.svg" /></a>
</p>

## What is this installer for?

This installer creates a fully working Modseven installation. 

It creates the `public` and `application` directory with all required files and a demo `Welcome` Controller.
It also installs modseven via composer and adds a working `composer.json` for your project.

Since it installs the `modseven/core` package directly via composer it is very easy to manage your modseven installation and version.


## How to use?

First, download the Modseven installer using Composer:

`composer global require modseven/installer`

Make sure to place Composer's system-wide vendor bin directory in your $PATH so the modseven executable can be located by your system. 
This directory exists in different locations based on your operating system; however, some common locations include:

| Distribution  | Path                                                |
|---------------|-----------------------------------------------------|
| macOS / Linux | `$HOME/.config/composer/vendor/bin`                 |
| Windows       | `%USERPROFILE%\AppData\Roaming\Composer\vendor\bin` |

Once installed, the `modseven new` command will create a fresh Modseven installation in the directory you specify. 

For instance: 

`modseven new blog`

will create a directory named blog containing a fresh modseven installation with all of Modseven's dependencies already installed.


## Contributing

Any help is more than welcome! Just fork this repo and do a PR.

## Special Thanks

Special Thanks to all Contributors and the Community!
