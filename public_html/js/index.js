const ws = new WebSocket('ws://127.0.0.1:1337/');

ws.onopen = function(event) {
};

ws.onerror = function(error) {
    console.log('ERR: ', error);
};
ws.onclose = function() {
    console.log('INFO: Socket Closed');
};
ws.onmessage = function(event) {
    console.log('RECV: ', event.data);
};
