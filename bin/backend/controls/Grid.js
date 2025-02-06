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
            '$onGridDblClick',
            '$test'
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
                        header: QUILocale.get(lg, 'template.textSmtpSettingsServer'),
                        dataIndex: 'server',
                        dataType: 'string',
                        width: 200
                    }, {
                        header: QUILocale.get(lg, 'template.textSmtpSettingsPort'),
                        dataIndex: 'port',
                        dataType: 'string',
                        width: 50
                    }, {
                        header: QUILocale.get(lg, 'template.textSmtpSettingsSecurity'),
                        title: QUILocale.get(lg, 'template.textSmtpSettingsSecurity'),
                        dataIndex: 'security',
                        dataType: 'string',
                        width: 50
                    }, {
                        header: QUILocale.get(lg, 'template.mailFrom'),
                        title: QUILocale.get(lg, 'template.mailFrom'),
                        dataIndex: 'MAILFrom',
                        dataType: 'string',
                        width: 200
                    }, {
                        header: QUILocale.get(lg, 'template.textAuth'),
                        title: QUILocale.get(lg, 'template.textAuth'),
                        dataIndex: 'display_auth',
                        dataType: 'node',
                        width: 50
                    }, {
                        header: QUILocale.get(lg, 'template.textSmtpSettingsSecureSSL_verify_peer'),
                        title: QUILocale.get(lg, 'template.textSmtpSettingsSecureSSL_verify_peer'),
                        dataIndex: 'display_secureSSL_verify_peer',
                        dataType: 'node',
                        width: 50
                    }, {
                        header: QUILocale.get(lg, 'template.textSmtpSettingsSecureSSL_verify_peer_name'),
                        title: QUILocale.get(lg, 'template.textSmtpSettingsSecureSSL_verify_peer_name'),
                        dataIndex: 'display_secureSSL_verify_peer_name',
                        dataType: 'node',
                        width: 50
                    }, {
                        header: QUILocale.get(lg, 'template.textSmtpSettingsSecureSSL_allow_self_signed'),
                        title: QUILocale.get(lg, 'template.textSmtpSettingsSecureSSL_allow_self_signed'),
                        dataIndex: 'display_secureSSL_allow_self_signed',
                        dataType: 'node',
                        width: 50
                    }, {
                        header: QUILocale.get(lg, 'template.textIsFallbackServer'),
                        title: QUILocale.get(lg, 'template.textIsFallbackServer'),
                        dataIndex: 'display_isFallbackServer',
                        dataType: 'node',
                        width: 50
                    }, {
                        hidden: true,
                        dataIndex: 'MAILFromText'
                    }, {
                        hidden: true,
                        dataIndex: 'MAILReplyTo'
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

                        servers[i].display_isFallbackServer = new Element('div', {
                            'class': parseInt(servers[i].isFallbackServer) ? 'fa fa-check' : 'fa fa-minus'
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
                QUIAjax.post('package_quiqqer_multi-mailer_ajax_backend_update', resolve, {
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
                icon: 'fa fa-envelope',
                maxHeight: 950,
                maxWidth: 600,
                autoclose: false,
                events: {
                    onOpen: (Win) => {
                        Win.Loader.show();
                        Win.getContent().set('html', Mustache.render(templateServer, {
                            textMailSettings: QUILocale.get(lg, 'template.mailSettings'),
                            textMailFrom: QUILocale.get(lg, 'template.mailFrom'),
                            textMailFromText: QUILocale.get(lg, 'template.mailFromText'),
                            textMailReplyTo: QUILocale.get(lg, 'template.mailReplyTo'),
                            textSmtpSettings: QUILocale.get(lg, 'template.textSmtpSettings'),
                            textSmtpSettingsServer: QUILocale.get(lg, 'template.textSmtpSettingsServer'),
                            textSmtpSettingsPort: QUILocale.get(lg, 'template.textSmtpSettingsPort'),
                            textSmtpSettingsDebug: QUILocale.get(lg, 'template.textSmtpSettingsDebug'),
                            textSmtpSettingsSecurity: QUILocale.get(lg, 'template.textSmtpSettingsSecurity'),
                            textSmtpSettingsSecuritySSL: QUILocale.get(lg, 'template.textSmtpSettingsSecuritySSL'),
                            textSmtpSettingsSecurityTLS: QUILocale.get(lg, 'template.textSmtpSettingsSecurityTLS'),
                            textSmtpSettingsSecureSSL_verify_peer: QUILocale.get(
                                lg,
                                'template.textSmtpSettingsSecureSSL_verify_peer'
                            ),
                            textSmtpSettingsSecureSSL_verify_peer_name: QUILocale.get(
                                lg,
                                'template.textSmtpSettingsSecureSSL_verify_peer_name'
                            ),
                            textSmtpSettingsSecureSSL_allow_self_signed: QUILocale.get(
                                lg,
                                'template.textSmtpSettingsSecureSSL_allow_self_signed'
                            ),
                            textIsFallbackServer: QUILocale.get(lg, 'template.textIsFallbackServer'),
                            textIsFallbackServerDescription: QUILocale.get(lg, 'template.textIsFallbackServer.description'),
                            textAuth: QUILocale.get(lg, 'template.textAuth'),
                            textAuthActivate: QUILocale.get(lg, 'template.textAuthActivate'),
                            textAuthUsername: QUILocale.get(lg, 'template.textAuthUsername'),
                            textAuthPassword: QUILocale.get(lg, 'template.textAuthPassword'),
                            textTestButton: QUILocale.get(lg, 'template.textTestButton')
                        }));

                        Win.getContent().querySelector('[name="test"]').addEventListener('click', this.$test);

                        Win.Loader.hide();
                    },
                    onSubmit: (Win) => {
                        const Form = Win.getContent().querySelector('form');
                        const data = this.$getDataFromForm(Form);

                        if (data.server === '') {
                            Form.elements.server.focus();
                            return;
                        }

                        this.$Grid.addRow(data);

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
                maxHeight: 950,
                maxWidth: 600,
                autoclose: false,
                events: {
                    onOpen: (Win) => {
                        Win.Loader.show();
                        Win.getContent().set('html', Mustache.render(templateServer, {
                            textMailSettings: QUILocale.get(lg, 'template.mailSettings'),
                            textMailFrom: QUILocale.get(lg, 'template.mailFrom'),
                            textMailFromText: QUILocale.get(lg, 'template.mailFromText'),
                            textMailReplyTo: QUILocale.get(lg, 'template.mailReplyTo'),
                            textSmtpSettings: QUILocale.get(lg, 'template.textSmtpSettings'),
                            textSmtpSettingsServer: QUILocale.get(lg, 'template.textSmtpSettingsServer'),
                            textSmtpSettingsPort: QUILocale.get(lg, 'template.textSmtpSettingsPort'),
                            textSmtpSettingsDebug: QUILocale.get(lg, 'template.textSmtpSettingsDebug'),
                            textSmtpSettingsSecurity: QUILocale.get(lg, 'template.textSmtpSettingsSecurity'),
                            textSmtpSettingsSecuritySSL: QUILocale.get(lg, 'template.textSmtpSettingsSecuritySSL'),
                            textSmtpSettingsSecurityTLS: QUILocale.get(lg, 'template.textSmtpSettingsSecurityTLS'),
                            textSmtpSettingsSecureSSL_verify_peer: QUILocale.get(
                                lg,
                                'template.textSmtpSettingsSecureSSL_verify_peer'
                            ),
                            textSmtpSettingsSecureSSL_verify_peer_name: QUILocale.get(
                                lg,
                                'template.textSmtpSettingsSecureSSL_verify_peer_name'
                            ),
                            textSmtpSettingsSecureSSL_allow_self_signed: QUILocale.get(
                                lg,
                                'template.textSmtpSettingsSecureSSL_allow_self_signed'
                            ),
                            textIsFallbackServer: QUILocale.get(lg, 'template.textIsFallbackServer'),
                            textIsFallbackServerDescription: QUILocale.get(lg, 'template.textIsFallbackServer.description'),
                            textAuth: QUILocale.get(lg, 'template.textAuth'),
                            textAuthActivate: QUILocale.get(lg, 'template.textAuthActivate'),
                            textAuthUsername: QUILocale.get(lg, 'template.textAuthUsername'),
                            textAuthPassword: QUILocale.get(lg, 'template.textAuthPassword'),
                            textTestButton: QUILocale.get(lg, 'template.textTestButton')
                        }));

                        Win.getContent().querySelector('[name="test"]').addEventListener('click', this.$test);

                        const Form = Win.getContent().querySelector('form');

                        Form.elements.MAILFrom.value = selected[0].MAILFrom;
                        Form.elements.MAILFromText.value = selected[0].MAILFromText;
                        Form.elements.MAILReplyTo.value = selected[0].MAILReplyTo;
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
                        Form.elements.isFallbackServer.checked = !!parseInt(selected[0].isFallbackServer);

                        Win.Loader.hide();
                    },
                    onSubmit: (Win) => {
                        Win.Loader.show();
                        const Form = Win.getContent().querySelector('form');

                        this.$Grid.setDataByRow(
                            this.$Grid.getSelectedIndices()[0],
                            this.$getDataFromForm(Form)
                        );

                        this.update().then(() => {
                            Win.close();
                        });
                    }
                }
            }).open();
        },

        $test: function(event) {
            event.preventDefault();
            event.stopPropagation();

            const SendButton = event.target;
            const window = SendButton.getParent('.qui-window-popup');
            const Window = QUI.Controls.getById(window.get('data-quiid'));
            const Form = window.querySelector('form');

            Window.Loader.show();

            QUIAjax.post('package_quiqqer_multi-mailer_ajax_backend_test', () => {
                Window.Loader.hide();
            }, {
                package: 'quiqqer/multi-mailer',
                serverData: JSON.encode(this.$getDataFromForm(Form))
            });

            return false;
        },

        $getDataFromForm: function(Form) {
            return {
                MAILFrom: Form.elements.MAILFrom.value,
                MAILFromText: Form.elements.MAILFromText.value,
                MAILReplyTo: Form.elements.MAILReplyTo.value,
                server: Form.elements.server.value,
                port: Form.elements.port.value,
                auth: Form.elements.auth.checked ? 1 : 0,
                username: Form.elements.username.value,
                password: Form.elements.password.value,
                security: Form.elements.security.value,
                debug: Form.elements.debug.value,
                secureSSL_verify_peer: Form.elements.secureSSL_verify_peer.checked ? 1 : 0,
                secureSSL_verify_peer_name: Form.elements.secureSSL_verify_peer_name.checked ? 1 : 0,
                secureSSL_allow_self_signed: Form.elements.secureSSL_allow_self_signed.checked ? 1 : 0,
                isFallbackServer: Form.elements.isFallbackServer.checked ? 1 : 0,
            };
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
