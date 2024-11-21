define('package/quiqqer/multi-mailer/bin/backend/controls/Grid', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/windows/Confirm',
    'controls/grid/Grid',

    'Mustache',
    'Locale',
    'Ajax',

    'text!package/quiqqer/multi-mailer/bin/backend/controls/smtp-mail-server.html'

], function(QUI, QUIControl, QUIConfirm, Grid, Mustache, QUILocale, QUIAjax, templateServer) {
    'use strict';

    const lg = 'quiqqer/multi-mailer';

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/multi-mailer/bin/backend/controls/Grid',

        Binds: [
            'addMailServer',
            'removeMailServer',
            'refresh',
            'update',

            '$onImport',
            '$onGridClick',
            '$onGridDblClick'
        ],

        initialize: function() {
            this.parent();

            this.$Grid = null;

            this.addEvents({
                onImport: this.$onImport
            });
        },

        $onImport: function() {
            const Container = new Element('div');
            Container.inject(this.$Elm, 'after');

            this.$Grid = new Grid(Container, {
                'button-reload': true,
                height: 400,
                buttons: [
                    {
                        name: 'add',
                        textimage: 'fa fa-plus',
                        text: 'Hinzufügen',
                        events: {
                            click: this.addMailServer
                        }
                    }, {
                        name: 'remove',
                        icon: 'fa fa-trash',
                        disabled: true,
                        position: 'right',
                        events: {
                            click: this.removeMailServer
                        }
                    }
                ],
                columnModel: [
                    {
                        header: 'Server',
                        dataIndex: 'server',
                        dataType: 'string',
                        width: 200
                    }, {
                        header: 'Port',
                        dataIndex: 'port',
                        dataType: 'string',
                        width: 100
                    }, {
                        header: 'Verbindungssicherheit',
                        dataIndex: 'security',
                        dataType: 'string',
                        width: 100
                    }, {
                        header: 'Auth',
                        dataIndex: 'display_auth',
                        dataType: 'node',
                        width: 50
                    }, {
                        header: 'Verifizierung des verwendeten SSL-Zertifikats voraussetzen',
                        title: 'Verifizierung des verwendeten SSL-Zertifikats voraussetzen',
                        dataIndex: 'display_secureSSL_verify_peer',
                        dataType: 'node',
                        width: 50
                    }, {
                        header: 'Erfordere die Verfikation des Peernamens',
                        title: 'Erfordere die Verfikation des Peernamens',
                        dataIndex: 'display_secureSSL_verify_peer_name',
                        dataType: 'node',
                        width: 50
                    }, {
                        header: 'Selbst signierte Zerifikate erlauben',
                        title: ' Selbst signierte Zerifikate erlauben',
                        dataIndex: 'display_secureSSL_allow_self_signed',
                        dataType: 'node',
                        width: 50
                    }, {
                        hidden: true,
                        dataIndex: 'auth'
                    }, {
                        hidden: true,
                        dataIndex: 'secureSSL_verify_peer'
                    }, {
                        hidden: true,
                        dataIndex: 'secureSSL_verify_peer_name'
                    }, {
                        hidden: true,
                        dataIndex: 'secureSSL_allow_self_signed'
                    }, {
                        hidden: true,
                        dataIndex: 'debug'
                    }, {
                        hidden: true,
                        dataIndex: 'username'
                    }, {
                        hidden: true,
                        dataIndex: 'password'
                    }
                ]
            });

            Container.setStyle('marginBottom', '10px');
            Container.setStyle('width', '100%');

            this.$Grid.addEvents({
                refresh: this.refresh,
                click: this.$onGridClick,
                dblClick: this.$onGridDblClick
            });

            this.$Grid.refresh();
        },

        refresh: function() {
            if (!this.$Grid) {
                return Promise.resolve();
            }

            this.$Grid.showLoader();

            return new Promise((resolve, reject) => {
                QUIAjax.get('package_quiqqer_multi-mailer_ajax_backend_getList', (servers) => {
                    servers.forEach((entry, i) => {
                        servers[i].display_auth = new Element('div', {
                            'class': parseInt(servers[i].auth) ? 'fa fa-check' : 'fa fa-minus'
                        });

                        servers[i].display_secureSSL_verify_peer = new Element('div', {
                            'class': parseInt(servers[i].secureSSL_verify_peer) ? 'fa fa-check' : 'fa fa-minus'
                        });

                        servers[i].display_secureSSL_verify_peer_name = new Element('div', {
                            'class': parseInt(servers[i].secureSSL_verify_peer_name) ? 'fa fa-check' : 'fa fa-minus'
                        });

                        servers[i].display_secureSSL_allow_self_signed = new Element('div', {
                            'class': parseInt(servers[i].secureSSL_allow_self_signed) ? 'fa fa-check' : 'fa fa-minus'
                        });
                    });


                    this.$Grid.setData({
                        data: servers
                    });

                    this.$Grid.hideLoader();
                    resolve();
                }, {
                    package: 'quiqqer/multi-mailer',
                    onError: reject
                });
            });
        },

        update: function() {
            this.$Grid.showLoader();

            return new Promise((resolve, reject) => {
                QUIAjax.get('package_quiqqer_multi-mailer_ajax_backend_update', resolve, {
                    package: 'quiqqer/multi-mailer',
                    servers: JSON.encode(this.$Grid.getData()),
                    onError: reject
                });
            }).then(() => {
                return this.refresh();
            });
        },

        // edit stuff

        addMailServer: function() {
            new QUIConfirm({
                title: QUILocale.get(lg, 'window.addMailServer.title'),
                maxHeight: 800,
                maxWidth: 600,
                events: {
                    onOpen: (Win) => {
                        Win.Loader.show();
                        Win.getContent().set('html', Mustache.render(templateServer, {}));

                        Win.Loader.hide();
                    },
                    onSubmit: (Win) => {
                        const Form = Win.getContent().querySelector('form');

                        this.$Grid.addRow({
                            server: Form.elements.server.value,
                            port: Form.elements.port.value,
                            auth: Form.elements.auth.checked ? 1 : 0,
                            username: Form.elements.username.value,
                            password: Form.elements.password.value,
                            security: Form.elements.security.value,
                            debug: Form.elements.debug.value,
                            secureSSL_verify_peer: Form.elements.secureSSL_verify_peer.checked ? 1 : 0,
                            secureSSL_verify_peer_name: Form.elements.secureSSL_verify_peer_name.checked ? 1 : 0,
                            secureSSL_allow_self_signed: Form.elements.secureSSL_allow_self_signed.checked ? 1 : 0
                        });

                        this.update().then(() => {
                            Win.close();
                        });
                    }
                }
            }).open();
        },

        removeMailServer: function() {
            const selected = this.$Grid.getSelectedData();

            if (selected.length !== 1) {
                return;
            }

            new QUIConfirm({
                icon: 'fa fa-trash',
                texticon: 'fa fa-trash',
                title: QUILocale.get(lg, 'window.removeMailServer.title'),
                information: QUILocale.get(lg, 'window.removeMailServer.information'),
                text: QUILocale.get(lg, 'window.removeMailServer.text'),
                maxHeight: 400,
                maxWidth: 600,
                autoclose: false,
                events: {
                    onOpen: (Win) => {
                        new Element('div', {
                            html: '<ul><li>' + selected[0].server + ':' + selected[0].port + '</li></ul>'
                        }).inject(Win.getContent().getElement('.information'));
                    },
                    onSubmit: (Win) => {
                        Win.Loader.show();

                        this.$Grid.deleteRows(
                            this.$Grid.getSelectedIndices()
                        );

                        this.update().then(() => {
                            Win.close();
                        });
                    }
                }
            }).open();
        },

        editMailServer: function() {
            const selected = this.$Grid.getSelectedData();

            if (selected.length !== 1) {
                return;
            }

            new QUIConfirm({
                icon: 'fa fa-edit',
                texticon: 'fa fa-edit',
                title: QUILocale.get(lg, 'window.editMailServer.title'),
                maxHeight: 800,
                maxWidth: 600,
                autoclose: false,
                events: {
                    onOpen: (Win) => {
                        Win.Loader.show();
                        Win.getContent().set('html', Mustache.render(templateServer, {}));

                        const Form = Win.getContent().querySelector('form');

                        Form.elements.server.value = selected[0].server;
                        Form.elements.port.value = selected[0].port;
                        Form.elements.auth.checked = !!parseInt(selected[0].auth);
                        Form.elements.username.value = selected[0].username;
                        Form.elements.password.value = selected[0].password;
                        Form.elements.security.value = selected[0].security;
                        Form.elements.debug.value = selected[0].debug;
                        Form.elements.secureSSL_verify_peer.checked = !!parseInt(selected[0].secureSSL_verify_peer);
                        Form.elements.secureSSL_verify_peer_name.checked = !!parseInt(selected[0].secureSSL_verify_peer_name);
                        Form.elements.secureSSL_allow_self_signed.checked = !!parseInt(selected[0].secureSSL_allow_self_signed);

                        Win.Loader.hide();
                    },
                    onSubmit: (Win) => {
                        Win.Loader.show();
                        const Form = Win.getContent().querySelector('form');

                        this.$Grid.setDataByRow(this.$Grid.getSelectedIndices()[0], {
                            server: Form.elements.server.value,
                            port: Form.elements.port.value,
                            auth: Form.elements.auth.checked ? 1 : 0,
                            username: Form.elements.username.value,
                            password: Form.elements.password.value,
                            security: Form.elements.security.value,
                            debug: Form.elements.debug.value,
                            secureSSL_verify_peer: Form.elements.secureSSL_verify_peer.checked ? 1 : 0,
                            secureSSL_verify_peer_name: Form.elements.secureSSL_verify_peer_name.checked ? 1 : 0,
                            secureSSL_allow_self_signed: Form.elements.secureSSL_allow_self_signed.checked ? 1 : 0
                        });

                        this.update().then(() => {
                            Win.close();
                        });
                    }
                }
            }).open();
        },

        //endregion

        // events

        $onGridClick: function() {
            const Remove = this.$Grid.getButton('remove');
            const selected = this.$Grid.getSelectedData();

            if (selected.length === 1) {
                Remove.enable();
            } else {
                Remove.disable();
            }
        },

        $onGridDblClick: function() {
            const selected = this.$Grid.getSelectedData();

            if (selected.length === 1) {
                this.editMailServer();
            }
        }

        //endregion
    });
});
