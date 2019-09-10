require('./polyfill.js');
window.$ = window.jQuery = require('jquery');
require('pace-progress');
window._ = require('lodash');
window.tmpl = require('blueimp-tmpl');
window.moment = require('moment');
require('eonasdan-bootstrap-datetimepicker');

var qsLib = require('qs');

$.extend({
  csrf: function (setting, resolve, reject) {
    setting.headers = _.assign(setting.headers ? setting.headers : {}, {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    });

    $.ajax(setting).then((data, textStatus, jqXHR) => {
      if (_.isFunction (resolve)) {
        resolve(data, textStatus, jqXHR);
      }
    }, (jqXHR, textStatus, errorThrown) => {
      console.log(setting);

      switch (jqXHR.status) {
        case 400:
          $.alertError(jqXHR.responseJSON.message);

          break;
        case 500:
          $.alertError('服务器错误');

          break;
        default:
          $.alertError(jqXHR.statusText);

          break;
      }

      if (_.isFunction (reject)) {
        reject(jqXHR, textStatus, errorThrown);
      }
    });
  },
  alertSuccess: function (content, options) {
    if (!content) {
      return;
    }

    var _options = {
      duration: 1500,
      complete: function () {},
    };

    if (_.isFunction (options)) {
      _options.complete = options;
    } else if (_.isPlainObject(options)) {
      _.assign(_options, options);
    }

    var alert = `
      <div class="alert alert-success alert-message-box">
        <p>` + content + `</p>
      </div>
    `;

    $(alert).prependTo('body');

    setTimeout(() => {
      $('.alert-message-box').last().remove();

      if (_.isFunction (_options.complete)) {
        _options.complete();
      }
    }, _options.duration);
  },
  alertError: function (content, options) {
    if (!content) {
      return;
    }

    var _options = {
      duration: 1500,
      complete: function () {},
    };

    if (_.isFunction (options)) {
      _options.complete = options;
    } else if (_.isPlainObject(options)) {
      _.assign(_options, options);
    }

    var alert = `
      <div class="alert alert-danger alert-message-box">
        <p>` + content + `</p>
      </div>
    `;

    $(alert).prependTo('body');

    setTimeout(() => {
      $('.alert-message-box').last().remove();

      if (_.isFunction (_options.complete)) {
        _options.complete();
      }
    }, _options.duration);
  },
  buildQS: function (query, mergeQuery) {
    var qs = '';

    if (_.isPlainObject(query)) {
      if (_.isPlainObject(mergeQuery)) {
        _.assign(query, mergeQuery);
      }

      qs = qsLib.stringify(query);
    }

    return qs;
  },
  parseQS: function (qs) {
    if (_.isString(qs)) {
      return qsLib.parse(qs, {
        plainObjects: true,
      });
    } else {
      return qsLib.parse(window.location.search.substr(1), {
        plainObjects: true,
      });
    }
  },
  buildURL: function (path, query, mergeQuery) {
    var url = window.location.origin;

    if (path) {
      if (path.startsWith('/')) {
        url += path;
      } else {
        url += '/' + path;
      }
    }

    var qs = $.buildQS(query, mergeQuery);

    if (qs) {
      url += '?' + qs;
    }

    return url;
  },
});
