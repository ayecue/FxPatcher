# FxPatcher

> Compatible with Pimcore 3.x.x

## Description

This extension automaticly patches Pimcore JavaScript files in runtime. This is the reason why it's save against Pimcore updates.

Just imagine you want to add a scrollbar to the brick toolbar in Pimcore. You will see that you need to manipulate the source code of Pimcore to make it happen. With this Plugin you don't need to manipulate the source code but you can still add the scrollbar to the brick toolbar. This is because this Plugin don't touch the Pimcore source files it just overrides them during runtime.

## Installation

This extension creates a folder in the website directory with the name "FxPatcher". In this folder are two subfolders which are named "admin" and "document". In this two directories you can add your JavaScript patch files. 

* if you want to override functionality in the admin view you have to create a JavaScript file in "/website/FxPatcher/admin/<mypatch>.js" 
* if you want to override functionality in the document edit view you have to create a JavaScript file in "/website/FxPatcher/document/<mypatch>.js"

You are also able to rename the folder which you want to use for your patches. Just change the WebsiteSetting variable named "fxPatcherPath". By default it's "/FxPatcher/". The two subfolders "admin" and "document" will be created automaticly.

## Example:

I want to the possibility to see more than 4 languages in the translate view ("Extras > Translation > Shared Translation") of pimcore. 

The first thing I do is creating a JavaScript file in the "website/FxPatcher/admin" folder since it's a admin view. The name of the file should describe somehow what file it's patching. I name it "settings.translations.patch.js". So now I created a file here "website/FxPatcher/admin/settings.translations.patch.js".

Now, since I created the JavaScript patch file, I will add the code which is the following:
```
fxpatcher.add({
    library : 'pimcore.settings.translations',
    override : {
        myMaxCols: 999,

        getRowEditor: function () {

            var StateFullProvider = Ext.extend(Ext.state.Provider, {
                namespace: "default",

                constructor : function(config){
                    StateFullProvider.superclass.constructor.call(this);
                    Ext.apply(this, config);

                    var data = localStorage.getItem(this.namespace);
                    if (!data) {
                        this.state = {};
                    } else {
                        data = JSON.parse(data);
                        if (data.state && data.user == pimcore.currentuser.id) {
                            this.state = data.state;
                        } else {
                            this.state = {};
                        }
                    }
                },

                get : function(name, defaultValue){
                    try {
                        if (typeof this.state[name] == "undefined") {
                            return defaultValue
                        } else {
                            return this.decodeValue(this.state[name])
                        }
                    } catch (e) {
                        this.clear(name);
                        return defaultValue;
                    }
                },
                set : function(name, value){
                    try {
                        if (typeof value == "undefined" || value === null) {
                            this.clear(name);
                            return;
                        }
                        this.state[name] = this.encodeValue(value)

                        var data = {
                            state: this.state,
                            user: pimcore.currentuser.id
                        };
                        var json = JSON.stringify(data);

                        localStorage.setItem(this.namespace, json);
                    } catch (e) {
                        this.clear(name);
                    }

                    this.fireEvent("statechange", this, name, value);
                }
            });

            var provider = new StateFullProvider({
                namespace : "pimcore_ui_states"
            });


            Ext.state.Manager.setProvider(provider);

            var stateId = "tr_" + this.translationType;
            var applyInitialSettings = false;
            var showInfo = false;
            var state = provider.get(stateId, null);
            var languages = this.languages;

            var maxCols = this.myMaxCols;   // include creation date / modification date / action column)
            var maxLanguages = maxCols - 3;

            if (state == null) {
                applyInitialSettings = true;
                if (languages.length > maxLanguages) {
                    showInfo = true;
                }
            } else {
                if (state.columns) {
                    for (var i = 0; i < state.columns.length; i++) {
                        var colState = state.columns[i];
                        if (colState.hidden) {
                            showInfo = true;
                            break;
                        }
                    }
                }
            }


            var proxy = new Ext.data.HttpProxy({
                url: this.dataUrl,
                method: 'post'
            });

            var readerFields = [
                {name: 'key', allowBlank: false},
                {name: 'creationDate', allowBlank: true},
                {name: 'modificationDate', allowBlank: true}
            ];

            var typesColumns = [
                {header: t("key"), sortable: true, dataIndex: 'key', editable: false}

            ];

            for (var i = 0; i < languages.length; i++) {

                readerFields.push({name: languages[i]});
                var columnConfig = {header: pimcore.available_languages[languages[i]], sortable: false, dataIndex: languages[i],
                    editor: new Ext.form.TextField({}), id: "translation_column_" + languages[i].toLowerCase()};
                if (applyInitialSettings) {
                    var hidden = i >= maxLanguages;
                    columnConfig.hidden = hidden;
                }

                typesColumns.push(columnConfig);
            }

            if (showInfo) {
                pimcore.helpers.showNotification(t("info"), t("there_are_more_columns"), null, null, 2000);
            }

            typesColumns.push({header: t("creationDate"), sortable: true, dataIndex: 'creationDate', editable: false,
                renderer: function(d) {
                    var date = new Date(d * 1000);
                    return date.format("Y-m-d H:i:s");
                }});
            typesColumns.push({header: t("modificationDate"), sortable: true, dataIndex: 'modificationDate', editable: false,
                renderer: function(d) {
                    var date = new Date(d * 1000);
                    return date.format("Y-m-d H:i:s");
                }});

            typesColumns.push({
                xtype: 'actioncolumn',
                width: 30,
                items: [{
                    tooltip: t('delete'),
                    icon: "/pimcore/static/img/icon/cross.png",
                    handler: function (grid, rowIndex) {
                        grid.getStore().removeAt(rowIndex);
                    }.bind(this)
                }]
            });

            var reader = new Ext.data.JsonReader({
                totalProperty: 'total',
                successProperty: 'success',
                root: 'data',
                idProperty: 'key'
            }, readerFields);

            var writer = new Ext.data.JsonWriter();

            var itemsPerPage = 20;
            this.store = new Ext.data.Store({
                id: 'translation_store',
                restful: false,
                proxy: proxy,
                reader: reader,
                writer: writer,
                remoteSort: true,
                baseParams: {
                    limit: itemsPerPage,
                    filter: this.preconfiguredFilter
                },
                listeners: {
                    write : function(store, action, result, response, rs) {
                    }
                }
            });

            this.pagingtoolbar = new Ext.PagingToolbar({
                pageSize: itemsPerPage,
                store: this.store,
                displayInfo: true,
                displayMsg: '{0} - {1} / {2}',
                emptyMsg: t("no_items_found")
            });

            // add per-page selection
            this.pagingtoolbar.add("-");

            this.pagingtoolbar.add(new Ext.Toolbar.TextItem({
                text: t("items_per_page")
            }));
            this.pagingtoolbar.add(new Ext.form.ComboBox({
                store: [
                    [10, "10"],
                    [20, "20"],
                    [40, "40"],
                    [60, "60"],
                    [80, "80"],
                    [100, "100"]
                ],
                mode: "local",
                width: 50,
                value: 20,
                triggerAction: "all",
                listeners: {
                    select: function (box, rec, index) {
                        this.pagingtoolbar.pageSize = intval(rec.data.field1);
                        this.pagingtoolbar.moveFirst();
                    }.bind(this)
                }
            }));



            this.grid = new Ext.grid.EditorGridPanel({
                frame: false,
                autoScroll: true,
                store: this.store,
                columnLines: true,
                stripeRows: true,
                columns : typesColumns,
                trackMouseOver: true,
                bbar: this.pagingtoolbar,
                stateful: true,
                stateId: stateId,
                stateEvents: ['columnmove', 'columnresize', 'sortchange', 'groupchange'],
                sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
                tbar: [
                    {
                        text: t('add'),
                        handler: this.onAdd.bind(this),
                        iconCls: "pimcore_icon_add"
                    },
                    '-',{
                        text: this.getHint(),
                        xtype: "tbtext",
                        style: "margin: 0 10px 0 0;"
                    },
                    "->",
                    {
                        text: t('cleanup'),
                        handler: this.cleanup.bind(this),
                        iconCls: "pimcore_icon_cleanup"
                    },
                    "-",
                    {
                        text: t('merge_csv'),
                        handler: this.doMerge.bind(this),
                        iconCls: "pimcore_icon_merge"
                    },
                    "-",
                    {
                        text: t('import_csv'),
                        handler: this.doImport.bind(this),
                        iconCls: "pimcore_icon_import"
                    },
                    '-',
                    {
                        text: t('export_csv'),
                        handler: this.doExport.bind(this),
                        iconCls: "pimcore_icon_export"
                    },'-',{
                        text: t("filter") + "/" + t("search"),
                        xtype: "tbtext",
                        style: "margin: 0 10px 0 0;"
                    },this.filterField
                ],
                viewConfig: {
                    forceFit: true
                }
            });

            this.store.load();

            return this.grid;
        }
    }
});
```

After this is done reload your Pimcore and the fix should appear. You also easily patch JavaScript in documents. Just drop your file into the "document" subfolder.

Basically this plugin collects all the JavaScript patches and inject them to the admin and document view. 

You'll always just need to call "fxpatcher.add" in your patch file. There are also just two properties. The first property is "library" which is the name of the library you want to patch. The second property is "override". With this property you can override properties of the original JavaScript file you are patching. You can also add properties which are not there. Just like in the example I created a new Property which is named "myMaxCols".

So you see you got some possibilities to patch visuals in Pimcore instantly.

## How to install

* Create folder and drop files in "/plugins/FxPatcher"
* Go to extension menu and click enable and install