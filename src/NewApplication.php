<?php

namespace Modseven\Installer\Console;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class NewApplication extends Command
{
    /**
     * Configure the command options.
     */
    protected function configure() : void
    {
        $this
            ->setName('new')
            ->setDescription('Create a new Modseven application')
            ->addArgument('name', InputArgument::REQUIRED)
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces install even if the directory already exists');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        // Build directory name
        $name = $input->getArgument('name');

        $directory = $name && $name !== '.' ? getcwd().'/'.$name : getcwd();

        // Check if directory already exists
        if (! $input->getOption('force'))
        {
            $this->verifyApplicationDoesntExist($directory);
        }

        // Copy skeleton directories
        $output->writeln('<info>Crafting application...</info>');

        // Src Directory
        $skeleton = dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'skeleton';

        $this->copy($skeleton, $directory)
             ->replaceName($name, $directory)
             ->prepareWritableDirectories($directory, $output);

        // Build the composer install command
        $composer = $this->findComposer();

        $commands = [
            $composer.' install --no-scripts',
        ];

        if ($input->getOption('no-ansi'))
        {
            $commands = array_map(static function ($value) {
                return $value.' --no-ansi';
            }, $commands);
        }

        if ($input->getOption('quiet'))
        {
            $commands = array_map(static function ($value) {
                return $value.' --quiet';
            }, $commands);
        }

        // Execute the composer statement
        exec('cd ' . $directory . ' && ' .implode(' && ', $commands));

        $output->writeln('<comment>Application ready! Build something amazing.</comment>');
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param  string  $directory
     */
    protected function verifyApplicationDoesntExist($directory) : void
    {
        if ((is_dir($directory) || is_file($directory)) && $directory != getcwd())
        {
            throw new RuntimeException('Application already exists!');
        }
    }

    /**
     * Download the temporary Zip to the given file.
     *
     * @param  string  $src  Source Directory
     * @param  string  $dst  Target Directory
     *
     * @return self
     */
    protected function copy($src, $dst) : self
    {
        // open the source directory
        $dir = opendir($src);

        // Make the destination directory if not exist
        if ( ! mkdir($dst) && ! is_dir($dst))
        {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dst));
        }

        // Loop through the files in source directory
        while($file = readdir($dir))
        {
            if (( $file !== '.' ) && ( $file !== '..' ))
            {
                if ( is_dir($src . DIRECTORY_SEPARATOR . $file) )
                {
                    // Recursively calling copy function for sub directory
                    $this->copy($src . '/' . $file, $dst . '/' . $file);
                }
                else
                {
                    $copied = copy($src . '/' . $file, $dst . '/' . $file);

                    if (!$copied)
                    {
                        throw new RuntimeException(sprintf('There was a problem copying "%s"', $file));
                    }
                }
            }
        }

        closedir($dir);

        return $this;
    }

    /**
     * Replace the Name of the application inside specific files.
     *
     * @param string $appName  Application name
     * @param string $dir      Main Directory
     *
     * @return self
     */
    protected function replaceName(string $appName, string $dir) : self
    {
        $placeholder = '<AppName>';
        $files = [
            $dir . DIRECTORY_SEPARATOR . 'composer.json',
            $dir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'classes'
            . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . 'Welcome.php',
            $dir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'routes.php'
        ];

        foreach ($files as $file)
        {
            $this->replaceInFile($file, $placeholder, $appName);
        }

        return $this;
    }

    /**
     * Replace a string inside a file
     *
     * @param string $file File to replace string in
     * @param string $old  Old String
     * @param string $new  New String
     */
    protected function replaceInFile(string $file, string $old, string $new) : void
    {
        // Open File
        $str = file_get_contents($file);

        // Replace the string
        $str = str_replace($old, $new, $str);

        // Write to the file
        $success = file_put_contents($file, $str);

        if ($success === false)
        {
            throw new RuntimeException(sprintf('There was a problem preparing the file "%s"', $file));
        }
    }

    /**
     * Make sure the storage and bootstrap cache directories are writable.
     *
     * @param string          $appDirectory
     * @param OutputInterface $output
     *
     * @return self
     */
    protected function prepareWritableDirectories(string $appDirectory, OutputInterface $output) : self
    {
        $filesystem = new Filesystem;

        try
        {
            $filesystem->chmod($appDirectory . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'cache', 0755, 0000, true);
            $filesystem->chmod($appDirectory . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'logs', 0755, 0000, true);
        }
        catch (IOExceptionInterface $e)
        {
            $output->writeln('<comment>You should verify that the "application/logs" and "application/cache" directories are writable.</comment>');
        }

        return $this;
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer() : string
    {
        $composerPath = getcwd().'/composer.phar';

        if (file_exists($composerPath))
        {
            return '"'.PHP_BINARY.'" '.$composerPath;
        }

        return 'composer';
    }
}