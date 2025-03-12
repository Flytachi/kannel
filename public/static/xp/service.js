class Service
{
    static aXhr = [];

    static cXhrInit(key = 'default') {
        if(this.aXhr[key] && this.aXhr[key].readyState !== 4)
            this.aXhr[key].abort();
    }

    static isAuth(response) {
        if (response.status === 401) {
            InitSession()
        }
    }

    static sessionExist(
        notifyError = true,
        callbackSuccess = (response) => {},
        callbackError = (response) => {},
    ) {
        this.cXhrInit('auth')

        this.aXhr['auth'] = $.ajax({
            type: "GET",
            url: "/auth/session/exist",
            contentType: "application/json",
            dataType: "json",
            headers: {
                "Accept": "application/json"
            },
            success: function (response) {
                callbackSuccess(response)
            },
            error: function (response) {
                if (notifyError) {
                    showNotification(
                        'Error',
                        response.responseJSON.message ?? 'Unknown error',
                        'error'
                    );
                }
                callbackError(response)
            }
        });
    }

    static login(
        username, password,
        notifyError = true,
        callbackSuccess = (response) => {},
        callbackError = (response) => {},
    ) {
        this.cXhrInit('auth')

        this.aXhr['auth'] = $.ajax({
            type: "POST",
            url: "/auth/session",
            contentType: "application/json",
            dataType: "json",
            headers: {
                "Accept": "application/json"
            },
            data: JSON.stringify({
                username,
                password
            }),
            success: function (response) {
                callbackSuccess(response)
            },
            error: function (response) {
                if (notifyError) {
                    showNotification(
                        'Error',
                        response.responseJSON.message ?? 'Unknown error',
                        'error'
                    );
                }
                callbackError(response)
            }
        });
    }

    static logout(
        notifyError = true,
        callbackSuccess = (response) => {},
        callbackError = (response) => {},
    ) {
        this.cXhrInit('auth')

        this.aXhr['auth'] = $.ajax({
            type: "DELETE",
            url: "/auth/session",
            contentType: "application/json",
            dataType: "json",
            headers: {
                "Accept": "application/json"
            },
            success: function (response) {
                callbackSuccess(response)
            },
            error: function (response) {
                if (notifyError) {
                    showNotification(
                        'Error',
                        response.responseJSON.message ?? 'Unknown error',
                        'error'
                    );
                }
                callbackError(response)
            }
        });
    }

    static serviceStatus(
        notifyError = true,
        callbackSuccess = (response) => {},
        callbackError = (response) => {},
    ) {
        this.cXhrInit('service')

        this.aXhr['service'] = $.ajax({
            type: "GET",
            url: "/api/service",
            contentType: "application/json",
            dataType: "json",
            headers: {
                "Accept": "application/json"
            },
            success: function (response) {
                callbackSuccess(response)
            },
            error: function (response) {
                Service.isAuth(response);
                if (notifyError) {
                    showNotification(
                        'Error',
                        response.responseJSON.message ?? 'Unknown error',
                        'error'
                    );
                }
                callbackError(response)
            }
        });
    }

    static serviceStart(
        notifyError = true,
        callbackSuccess = (response) => {},
        callbackError = (response) => {},
    ) {
        this.cXhrInit('service')

        this.aXhr['service'] = $.ajax({
            type: "PUT",
            url: "/api/service",
            contentType: "application/json",
            dataType: "json",
            headers: {
                "Accept": "application/json"
            },
            success: function (response) {
                callbackSuccess(response)
            },
            error: function (response) {
                Service.isAuth(response);
                if (notifyError) {
                    showNotification(
                        'Error',
                        response.responseJSON.message ?? 'Unknown error',
                        'error'
                    );
                }
                callbackError(response)
            }
        });
    }

    static serviceStop(
        notifyError = true,
        callbackSuccess = (response) => {},
        callbackError = (response) => {},
    ) {
        this.cXhrInit('service')

        this.aXhr['service'] = $.ajax({
            type: "DELETE",
            url: "/api/service",
            contentType: "application/json",
            dataType: "json",
            headers: {
                "Accept": "application/json"
            },
            success: function (response) {
                callbackSuccess(response)
            },
            error: function (response) {
                Service.isAuth(response);
                if (notifyError) {
                    showNotification(
                        'Error',
                        response.responseJSON.message ?? 'Unknown error',
                        'error'
                    );
                }
                callbackError(response)
            }
        });
    }

    static serviceSubsStatus(
        notifyError = true,
        callbackSuccess = (response) => {},
        callbackError = (response) => {},
    ) {
        this.cXhrInit('service')

        this.aXhr['service'] = $.ajax({
            type: "GET",
            url: "/api/service/subs",
            contentType: "application/json",
            dataType: "json",
            headers: {
                "Accept": "application/json"
            },
            success: function (response) {
                callbackSuccess(response)
            },
            error: function (response) {
                Service.isAuth(response);
                if (notifyError) {
                    showNotification(
                        'Error',
                        response.responseJSON.message ?? 'Unknown error',
                        'error'
                    );
                }
                callbackError(response)
            }
        });
    }

    static serviceSubsStart(
        serviceName,
        notifyError = true,
        callbackSuccess = (response) => {},
        callbackError = (response) => {},
    ) {
        this.cXhrInit('service')

        this.aXhr['service'] = $.ajax({
            type: "PUT",
            url: "/api/service/subs/" + serviceName,
            contentType: "application/json",
            dataType: "json",
            headers: {
                "Accept": "application/json"
            },
            success: function (response) {
                callbackSuccess(response)
            },
            error: function (response) {
                Service.isAuth(response);
                if (notifyError) {
                    showNotification(
                        'Error',
                        response.responseJSON.message ?? 'Unknown error',
                        'error'
                    );
                }
                callbackError(response)
            }
        });
    }

    static serviceSubsStop(
        serviceName,
        notifyError = true,
        callbackSuccess = (response) => {},
        callbackError = (response) => {},
    ) {
        this.cXhrInit('service')

        this.aXhr['service'] = $.ajax({
            type: "DELETE",
            url: "/api/service/subs/" + serviceName,
            contentType: "application/json",
            dataType: "json",
            headers: {
                "Accept": "application/json"
            },
            success: function (response) {
                callbackSuccess(response)
            },
            error: function (response) {
                Service.isAuth(response);
                if (notifyError) {
                    showNotification(
                        'Error',
                        response.responseJSON.message ?? 'Unknown error',
                        'error'
                    );
                }
                callbackError(response)
            }
        });
    }

    static logFiles(
        notifyError = true,
        callbackSuccess = (response) => {},
        callbackError = (response) => {},
    ) {
        this.cXhrInit('logs_f')

        this.aXhr['logs_f'] = $.ajax({
            type: "GET",
            url: "/api/logs/files",
            contentType: "application/json",
            dataType: "json",
            headers: {
                "Accept": "application/json"
            },
            success: function (response) {
                callbackSuccess(response)
            },
            error: function (response) {
                Service.isAuth(response);
                if (notifyError) {
                    showNotification(
                        'Error',
                        response.responseJSON.message ?? 'Unknown error',
                        'error'
                    );
                }
                callbackError(response)
            }
        });
    }

    static logList(
        filename,
        limit = 1000,
        notifyError = true,
        callbackSuccess = (response) => {},
        callbackError = (response) => {},
    ) {
        this.cXhrInit('logs')

        this.aXhr['logs'] = $.ajax({
            type: "GET",
            url: "/api/logs",
            contentType: "application/json",
            dataType: "json",
            headers: {
                "Accept": "application/json"
            },
            data: {filename, limit},
            success: function (response) {
                callbackSuccess(response)
            },
            error: function (response) {
                Service.isAuth(response);
                if (notifyError) {
                    showNotification(
                        'Error',
                        response.responseJSON.message ?? 'Unknown error',
                        'error'
                    );
                }
                callbackError(response)
            }
        });
    }

    static environmentList(
        notifyError = true,
        callbackSuccess = (response) => {},
        callbackError = (response) => {},
    ) {
        this.cXhrInit('environment')

        this.aXhr['environment'] = $.ajax({
            type: "GET",
            url: "/api/environment",
            contentType: "application/json",
            dataType: "json",
            headers: {
                "Accept": "application/json"
            },
            success: function (response) {
                callbackSuccess(response)
            },
            error: function (response) {
                Service.isAuth(response);
                if (notifyError) {
                    showNotification(
                        'Error',
                        response.responseJSON.message ?? 'Unknown error',
                        'error'
                    );
                }
                callbackError(response)
            }
        });
    }

}