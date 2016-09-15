# Clone TYPO3 Extension

Copies an existing extension to a new extension key.

All files and folders of a given TYPO3 extension folder are copied to a new extension folder.
In every file the appearance of the extension key is replaced in it's variants like: my_ext, MyExt, ...

WARNING: the conversion is done by simple search and replace and might produce wrong results and could break your code!
Review the changed files!

Use at your own risk!!

## Install

typo3_clone_extension.php is a cli script. PHP has to be installed as cli.
    
You can run the script with php
    
    > php typo3_clone_extension.php
        
or make the script executable:

    > chmod +x typo3_clone_extension.php
    
and run it

    > ./typo3_clone_extension.php
    
Then you may copy it ...

    > cp typo3_clone_extension.php /usr/local/bin/typo3_clone_extension
    
and just run it with 
    
    > typo3_clone_extension
    
## Usage
    
Just call the script it should give you hints how to use it.

## Remarks

I used this code over the years and lately I changed it to exist as a cli. It worked for me fine. But if it kills your cat, don't blame me. I warned you!

## Contribute

- Send pull requests to the repository. <https://github.com/colorcube/typo3_clone_extension>
- Use the issue tracker for feedback and discussions. <https://github.com/colorcube/typo3_clone_extension/issues>