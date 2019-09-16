# Styla TYPO3 Extension
This TYPO3 extension allows to show the Styla content hub and / or landing pages with full SEO integration in a TYPO3 setup.

[This documentation page](https://docs.styla.com/) should provide you an overview of how Styla works in general. 

## Requirements
TYPO3 >= 7.6.31  
PHP >= 5.5  
MySQL >= 5.5

## Installation Composer Mode
* Use `composer require styladev/typo3` in typo3 folder

## Installation Classic Mode
* Create new extension folder `ec_styla` in `/typo3conf/ext/`
* Move repository content in folder `ec_styla`
* Inside the TYPO3 backend, go to Extensions and activate ec_styla

## Configuration

### Extension Configuration
Within the extension configuration (Admin Tools->Extensions->Ecentral Styla Integration) the rootpath to the Styla content needs to be set up. By default, this is set to `magazine` which is the default Styla content integration.

### Root Page Configuration
If you want to use something else than the default configuration, you can configure the extension to use other root paths. To do this, go to the extension configuration and edit the configuration of ec_styla. Enter every path, where the styla content plugin should be displayed, separated by commas.

### Content Page Configuration
Necessary meta elements are provided by Styla. This configuration has to be
done for every page on which the content hub plugin will be displayed and will hide meta elements generated by TYPO3.

    page.meta.robots =
    config.noPageTitle = 2
    
Please note that there are issues with hiding the page title in some versions of TYPO3. Further information is available
in issue [#85720](https://forge.typo3.org/issues/85720) of the TYPO3 core bug tracker.
    
## Plugins provided by this Extension
You can add Styla Plugins via the 'Add Content' option of the page module. 

### Content Hub
The content hub plugin will display a single content hub or Styla Landing Page. You only need to provide the content hub id and let Styla do the
magic. Please make sure the page title corresponds to your `plugin.tx_ecstyla_contenthub.settings.contenthub_segment` setting.
#### Metadata
It is possible to disable specific meta tags via the typoscript setup key `plugin.tx_ecstyla_contenthub.settings.disabled_meta_tags`. You can configure multiple tag names and properties by separating them with commas

### Teaser
The Teaser plugin allows you to feature a number of stories from your Styla content hub. You can set the number of items 
to be displayed, the size of the teaser images as well as the display mode. Available display modes are:
* List
* Tiles
* Cards
* Horizontal

## Signal Slots
The extension provides two signal slots: beforeProcessingSeoContent and beforeCheckingForRootPath. You can find
out more information about signal slots in the [official TYPO3 documentation](https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/Hooks/Concept/Index.html). 

## Caching
TYPO3 will cache Styla related content for up to 60 Minutes by default. You can clear the cache anytime by using TYPO3s 'Clear all
caches' option within the TYPO3 backend.

## Development / Test setup

see [docker/README.md](docker/README.md)
