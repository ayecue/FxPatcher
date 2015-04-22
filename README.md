# FXPatcher

> Compatible with Pimcore 3.x.x

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