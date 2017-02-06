Ext.define('Gpb.store.Logs', {
    extend: 'Ext.data.Store',

    alias: 'store.logs',

    fields: [
        'name', 'email', 'phone'
    ],

    // Use proxy for data access at the php_server_side
    proxy: {
        type: 'rest',
        url: '/php_server_side/getlog.php',
        reader: {
            type: 'json',
            // Json response contains the "tems" property as a root
            rootProperty: 'items'
        }
    },

    // The table should be sorted by these fields
    sorters: [{
        property: 'name',
        direction: 'ASC'
    }, {
        property: 'email',
        direction: 'DESC'
    }],

    // Use server-side sorting and filtering for possible pagination
    remoteSort: true,
    remoteFilter: true,

    autoLoad: true,
    autoSync: true
});
