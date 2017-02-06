/**
 * This view is an example list of visitors (ip, the first URL from, the last URL to, browser name, os.
 */
Ext.define('Gpb.view.main.List', {
    extend: 'Ext.grid.Panel',
    xtype: 'mainlist',
    // is required for filtering
    plugins: 'gridfilters',

    requires: [
        // is required for filtering
        'Ext.grid.filters.Filters',
        'Gpb.store.Logs'
    ],

    title: 'URL Logs',

    // Use Logs store with proxy for server-side data access
    store: {
        type: 'logs'
    },

    columns: [
        {
            text: 'IP',
            dataIndex: 'user_ip',
            filter: { // ip column should be filtered
                type: 'string',
                itemDefaults: {
                    emptyText: 'Search for…'
                }
            }
        },
        { text: 'Browser', dataIndex: 'browser', flex: 1 },
        { text: 'OS', dataIndex: 'os', flex: 1 },
        { text: 'First URL from', dataIndex: 'url_from', flex: 1 },
        { text: 'Last URL to', dataIndex: 'url_to', flex: 1 },
        { text: 'URLs count', dataIndex: 'urls_count', flex: 1 }
    ],

    listeners: {
        select: 'onItemSelected'
    }
});
