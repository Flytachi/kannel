<div class="header">
    <div class="title">Kannel Web interface</div>
    <div class="buttons">
        <div class="button" onclick="Logout()">✕</div>
    </div>
</div>

<div class="content" id="content" style="display: none">

    <div class="content-sidebar" id="sidebar">

        <div class="card" style="width: 90%">
            <div class="card-header">Sidebar</div>
            <div class="card-body">
                <ul class="sidebar-menu">
                    <li onclick="selectProcessor(this)">Processor</li>
                    <li onclick="selectService(this)">Services</li>
                    <li onclick="selectConfig(this)">Environment</li>
                    <li onclick="selectLog(this)">Logs</li>
                </ul>
            </div>
        </div>

    </div>

    <div class="content-display" id="display"></div>

</div>

<div class="modal modal-sm" id="modalLogin">
    <div class="modal-header">Login Kannel Web Interface</div>
    <form action="" onsubmit="Login(this)">
        <div class="modal-body">
            <div class="form-control">
                <label for="name">Username:</label>
                <input type="text" name="username" class="input" placeholder="Enter username" required>
            </div>
            <div class="form-control">
                <label for="name">Password:</label>
                <input type="password" name="password" class="input" placeholder="Enter password" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn">Login</button>
        </div>
    </form>
</div>

<script src="/static/main.js"></script>
<script>
    let logTimerId;

    function selectProcessor(elem = null) {
        if (elem == null || !elem.classList.contains("selected")) {
            display.innerHTML = '';

            Service.serviceStatus(true, (response) => {
                let result = '<div class="card" style="width: auto">';
                result += '<div class="card-header">Processor</div>';
                result += '<div class="card-body">';

                result += '<strong>Condition:</strong> <span class="badge">' + response.condition + '</span><br/>';
                result += '<strong>ClassName:</strong> ' + response.className + '<br/>';
                result += '<strong>Pid:</strong> ' + (response.pid == null
                        ? 'null'
                        : '<span class="badge" style="background: #0800ff;">' + response.pid + '</span>'
                )  + '<br/>';
                result += '<strong>StartedAt:</strong> ' + (response.startedAt == null
                        ? 'null'
                        : response.startedAt
                );

                result += '</div><div class="card-footer">';
                if (response.condition === 'passive') {
                    result += '<button class="card-button" onclick="serviceStart()">Start</button>';
                } else if (response.condition !== 'passive') {
                    result += '<button class="card-button" onclick="serviceStop()">Stop</button>';
                }
                result += '<button class="card-button" onclick="selectProcessor()">Reload</button>';
                result += '</div></div>';
                display.innerHTML = result;
                closeAction = function () {}
            });
        }
    }

    function selectService(elem = null) {
        if (elem == null || !elem.classList.contains("selected")) {
            display.innerHTML = '';

            Service.serviceSubsStatus(true, (response) => {
                let result = '<div class="card" style="width: auto">';
                result += '<div class="card-header">Services</div>';
                result += '<div class="card-body">';

                Object.entries(response).forEach(([name, info]) => {
                    console.log(`${name}: ${info}`);
                    result += `<fieldset><legend>${name}</legend>`;
                    result += '<strong>Condition:</strong> <span class="badge">' + info.condition + '</span><br/>';
                    result += '<strong>ClassName:</strong> ' + info.className + '<br/>';
                    result += '<strong>Pid:</strong> ' + (info.pid == null
                            ? 'null'
                            : '<span class="badge" style="background: #0800ff;">' + info.pid + '</span>'
                    )  + '<br/>';
                    result += '<strong>StartedAt:</strong> ' + (info.startedAt == null
                            ? 'null'
                            : info.startedAt
                    );
                    result += '</br><strong>Action:</strong> ';
                    if (info.condition === 'passive') {
                        result += `<button class="card-button" onclick="serviceSubsStart('${name}')">Start</button>`;
                    } else if (info.condition !== 'passive') {
                        result += `<button class="card-button" onclick="serviceSubsStop('${name}')">Stop</button>`;
                    }
                    result += '</fieldset>';
                });

                result += '</div><div class="card-footer">';
                result += '<button class="card-button" onclick="selectService()">Reload</button>';
                result += '</div></div>';
                display.innerHTML = result;
                closeAction = function () {}
            });
        }
    }

    function selectConfig(elem = null) {
        if (elem == null || !elem.classList.contains("selected")) {
            display.innerHTML = '';

            Service.environmentList(true, (response) => {
                let result = '<div class="card" style="width: auto">';
                result += '<div class="card-header">Environment</div>';
                result += '<div class="card-body">';
                result += '<div class="log-container" id="logContainer" style="white-space: normal; height: 72vh">';

                response.forEach(function(element) {
                    result += '<div class="form-control" style="border-bottom: 2px dotted #808080;">';
                    result += `<label for="ID-${element.key}"><b>${element.key}</b></label>`;
                    result += `<input type="text" id="ID-${element.key}" name="${element.key}" class="input" value="${element.value}" style="width: 600px" disabled>`;
                    result += '</div>';
                });

                result += '</div>';
                result += '</div><div class="card-footer">';
                result += '<button class="card-button" onclick="selectConfig()">Reload</button>';
                // result += '<button class="card-button" onclick="">Save</button>';
                result += '</div></div>';
                display.innerHTML = result;
                closeAction = function () {}
            });
        }
    }

    function selectLog(elem = null) {
        if (elem == null || !elem.classList.contains("selected")) {
            display.innerHTML = '';

            Service.logFiles(true, (response) => {
                let result = '<div class="card" style="width: auto;">';
                result += '<div class="card-header">Logs</div>';
                result += '<div class="card-body">';
                result += '<div class="form-control" style="margin-bottom: 5px">';
                result += '<div class="">';
                result += '<label for="logParamFileName" style="margin-right: 5px">File:</label>';
                result += '<select class="xp-select" id="logParamFileName" onchange="showLogs()">';

                response.forEach(function(element) {
                    result += `<option value="${element}">${element}</option>`;
                });

                result += '</select>';
                result += '<label for="logParamLimit" style="margin-left: 10px;margin-right: 5px">Limit:</label>';
                result += '<input type="number" id="logParamLimit" class="input" min="50" max="10000000" step="1" value="1000" placeholder="Enter Limit">';
                result += '</div>'
                result += '<div class="">';
                result += '<label for="logParamTimeout" style="margin-right: 5px">Auto request:</label>';
                result += '<select class="xp-select" id="logParamTimeout" style="width: 30px" onchange="setTimer()">';
                result += `<option value="30">30</option>`;
                result += `<option value="10">10</option>`;
                result += `<option value="5">5</option>`;
                result += '</select> sec';
                result += '</div></div>';
                result += '<div class="log-container" id="logContainer" style="height: 72vh">';
                result += '<span class="log-entry">No data.</span>';
                result += '</div>';
                result += '</div><div class="card-footer">';
                result += '<button class="card-button" onclick="selectLog()">Reload All</button>';
                result += '<button class="card-button" onclick="showLogs()">Refresh</button>';
                result += '<button class="card-button" id="logTimeoutBtn" onclick=""></button>';
                result += '</div></div>';
                display.innerHTML = result;
                closeAction = function () {
                    unsetTimer();
                }
                setTimer();
                showLogs();
            });
        }
    }


    function setTimer() {
        let limit = Number.parseInt(document.querySelector("#logParamTimeout").value) * 1000;
        logTimerId = setInterval(showLogs, limit);
        let btn = document.querySelector("#logTimeoutBtn");
        if (btn) {
            btn.innerHTML = 'Auto (stop)'
            btn.onclick = unsetTimer;
        }
    }

    function unsetTimer() {
        clearInterval(logTimerId);
        let btn = document.querySelector("#logTimeoutBtn");
        if (btn) {
            btn.innerHTML = 'Auto (start)'
            btn.onclick = setTimer;
        }
    }

    function showLogs(reset = false) {
        if (reset) unsetTimer();
        let logContainer = document.querySelector("#logContainer");
        if (logContainer) {
            let filename = document.querySelector("#logParamFileName").value;
            let limit = document.querySelector("#logParamLimit").value;

            if (filename) {
                logContainer.innerHTML = '<span class="log-entry">Loading...</span>'
                Service.logList(filename, limit, true, (response) => {
                    let result = '';
                    response.forEach(function(element) {
                        result += `<span class="log-entry log-${element.level}">${element.message}</span>`;
                    });
                    logContainer.innerHTML = result;
                    logContainer.scrollTo(0, logContainer.scrollHeight);
                });
            } else {
                logContainer.innerHTML = '<span class="log-entry">No file data.</span>'
            }
        }
    }

</script>