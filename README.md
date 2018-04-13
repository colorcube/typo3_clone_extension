# Clone TYPO3 Extension

Copies an existing extension to a new extension key.

All files and folders of a given TYPO3 extension folder are copied to a new extension folder.
In every file the appearance of the extension key is replaced in it's variants like: `my_ext`, `MyExt`, …

:exclamation: WARNING: the conversion is done by simple search and replace and 
might produce wrong results and could break your code! Review the changed files! 
Use at your own risk!

## Install

`typo3_clone_extension` is a cli script. PHP has to be installed as cli.
    
You can simply clone the repository and run the script with PHP

    > php typo3_clone_extension.php
        
or add the script to your global scripts

    > chmod +x typo3_clone_extension.php
    > cp typo3_clone_extension.php /usr/local/bin/typo3_clone_extension
   
…and just run it with 
    
    > typo3_clone_extension
    
## Usage

If installed globally, then just run `typo3_clone_extension` to see all 
available options.
    
Example usage

    > typo3_clone_extension path/to/my_ext path/to/new_ext

## Remarks

I used this code over the years and lately I changed it to exist as a cli. 
It worked for me fine. But if it kills your cat, don't blame me. I warned you!

## License

GNU General Public License, version 2 or later

## Contribute

- Send pull requests to the repository. <https://github.com/colorcube/typo3_clone_extension>
- Use the issue tracker for feedback and discussions. <https://github.com/colorcube/typo3_clone_extension/issues>
