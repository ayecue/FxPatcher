# FxPatcher

> Compatible with Pimcore 3.x.x

## Description

Allows you to easily patch pimcore JS files. For example:
```
fxpatcher.add({
    library : 'pimcore.document.tags.areablock',
    override : {
        createToolBar: function () {
        	//code
        }
    }
});
```

Just look at the areablock example in the plugin.

## How to install

* Create folder and drop files in "/plugins/FxPatcher"
* Go to extension menu and click enable