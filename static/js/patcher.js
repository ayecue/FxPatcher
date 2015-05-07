(function(global,factory){
    global.fxpatcher = factory;
})(window,(function(global){
    var pimcore = global.pimcore;

    function FxPatcher(){
        this.map = {};
        this.stack = [];
        this.inject();
    }

    FxPatcher.getNs = function(path,root) {
        var splitted;

        if (!root) {
            root = global;
        }

        splitted = path instanceof Array ? path : path.split('.');

        while (root) {
            var prop = splitted.shift();

            if (prop in root) {
                root = root[prop];
            } else {
                return;
            }

            if (splitted.length == 0) {
                break;
            }
        }

        return root;
    };

    FxPatcher.patchNs = function(path,override){
        var me = this,
            splitted = path.split('.'),
            last = splitted.pop(),
            obj = me.getNs(path),
            parent = me.getNs(splitted);

        if (obj) {
            console.info('fxpatcher','patching',path);
            Ext.override(obj,override);

            me.watch(parent,last.toString(),function(prop,oldval,val){
                Ext.override(obj,override);
            });
        }

        return !!obj;
    };

    FxPatcher.watch = function (obj ,prop, handler) {
        var oldval = obj[prop], 
            newval = oldval,
            getter = function () {
                return newval;
            },
            setter = function (val) {
                oldval = newval;
                return newval = handler.call(obj, prop, oldval, val);
            };

        if (delete obj[prop]) { // can't watch constants
            if (Object.defineProperty) // ECMAScript 5
                Object.defineProperty(obj, prop, {
                    get: getter,
                    set: setter
                });
            else if (Object.prototype.__defineGetter__ && Object.prototype.__defineSetter__) { // legacy
                Object.prototype.__defineGetter__.call(obj, prop, getter);
                Object.prototype.__defineSetter__.call(obj, prop, setter);
            }
        }
    };

    FxPatcher.prototype = {
        self : FxPatcher,
        add : function(){
            var me = this;

            for (var index = 0, len = arguments.length; index < len; index++) {
                var patch = arguments[index];

                if (me.self.patchNs(patch.library,patch.override)) {
                    continue;
                }

                console.info('fxpatcher added', patch.library);
                me.map[patch.library] = patch.override;
                me.stack.push(patch);
            }
        },
        inject : function(){
            if (!pimcore) {
                throw new Error('No pimcore found');
            }

            var me = this,
                nativeRegisterNS = pimcore.registerNS;

            pimcore.registerNS = function(namespace) {
                var currentLevel = nativeRegisterNS.apply(this,arguments);

                if (namespace in me.map) {
                    me.self.patchNs(namespace,me.map[namespace]);
                }

                return currentLevel;
            };
        }
    };

    return new FxPatcher();
})(window));