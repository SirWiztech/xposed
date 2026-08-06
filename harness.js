function makeEl(id) {
  var cls = {};
  return {
    id: id || '',
    className: '',
    value: '',
    disabled: false,
    innerHTML: '',
    textContent: '',
    dataset: {},
    selectedOptions: [],
    style: { display: '' },
    _cls: cls,
    classList: {
      add: function (c) { cls[c] = 1; },
      remove: function (c) { delete cls[c]; },
      contains: function (c) { return !!cls[c]; },
      toggle: function (c, f) {
        if (f === undefined) { if (cls[c]) { delete cls[c]; return false; } cls[c] = 1; return true; }
        if (f) cls[c] = 1; else delete cls[c]; return !!f;
      }
    },
    addEventListener: function () {},
    reset: function () {},
    querySelector: function () { return makeEl('nested'); },
    closest: function () { return null; },
    getContext: function () { return {}; },
    getAttribute: function (a) { return this['data-' + a]; },
    setAttribute: function (a, v) { this['data-' + a] = v; }
  };
}

var els = {};
global.document = {
  getElementById: function (id) { if (!els[id]) els[id] = makeEl(id); return els[id]; },
  querySelector: function () { return makeEl('qs'); },
  querySelectorAll: function () { return []; },
  addEventListener: function (type, fn) { (this._handlers = this._handlers || {})[type] = (this._handlers[type] || []).concat([fn]); },
  fonts: { ready: { then: function () {} } }
};
global.localStorage = {
  _d: {},
  getItem: function (k) { return this._d[k] === undefined ? null : this._d[k]; },
  setItem: function (k, v) { this._d[k] = String(v); },
  removeItem: function (k) { delete this._d[k]; }
};
global.window = global;
global.confirm = function () { return true; };
global.Chart = undefined;
global.fetch = function () { return Promise.reject(new Error('no fetch')); };

var fs = require('fs');
var src = fs.readFileSync('C:/wamp64/www/xposed/assets/js/tools.js', 'utf8');

try {
  (0, eval)(src);
  console.log('INIT OK');
} catch (e) {
  console.log('INIT ERROR:', e.message);
  console.log(e.stack.split('\n').slice(0, 6).join('\n'));
}

var handlers = document._handlers && document._handlers.click;
var tabBtn = makeEl('tab');
tabBtn.closest = function (sel) { return (sel === '.tools-tab') ? tabBtn : null; };
tabBtn['data-tab'] = 'sessions';
if (handlers && handlers.length) {
  try {
    handlers.forEach(function (h) { h({ target: tabBtn }); });
    console.log('ALL CLICK HANDLERS OK');
  } catch (e) {
    console.log('CLICK ERROR:', e.message);
    console.log(e.stack.split('\n').slice(0, 6).join('\n'));
  }
} else {
  console.log('NO CLICK HANDLER REGISTERED');
}
