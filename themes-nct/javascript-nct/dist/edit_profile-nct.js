

//------------------------------------------------------
/* ===================================================
 * tagmanager.js v3.0.2
 * http://welldonethings.com/tags/manager
 * ===================================================
 * Copyright 2012 Max Favilli
 *
 * Licensed under the Mozilla Public License, Version 2.0 You may not use this work except in compliance with the License.
 *
 * http://www.mozilla.org/MPL/2.0/
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */
(function ($) {

  "use strict";

  var defaults = {
    prefilled: null,
    CapitalizeFirstLetter: false,
    preventSubmitOnEnter: true,     // deprecated
    isClearInputOnEsc: true,        // deprecated
    externalTagId: false,
    prefillIdFieldName: 'Id',
    prefillValueFieldName: 'Value',
    AjaxPush: null,
    AjaxPushAllTags: null,
    AjaxPushParameters: null,
    delimiters: [9, 13, 44],        // tab, enter, comma
    backspace: [8],
    maxTags: 0,
    hiddenTagListName: null,        // deprecated
    hiddenTagListId: null,          // deprecated
    replace: true,
    output: null,
    deleteTagsOnBackspace: true,    // deprecated
    tagsContainer: null,
    tagCloseIcon: 'x',
    tagClass: '',
    validator: null,
    onlyTagList: false,
    tagList: null,
    fillInputOnTagRemove: false
  },

  publicMethods = {
    pushTag: function (tag, ignoreEvents, externalTagId) {
      var $self = $(this), opts = $self.data('opts'), alreadyInList, tlisLowerCase, max, tagId,
      tlis = $self.data("tlis"), tlid = $self.data("tlid"), idx, newTagId, newTagRemoveId, escaped,
      html, $el, lastTagId, lastTagObj;

      tag = privateMethods.trimTag(tag, opts.delimiterChars);

      if (!tag || tag.length <= 0) { return; }

      // check if restricted only to the tagList suggestions
      if (opts.onlyTagList && undefined !== opts.tagList) {

        //if the list has been updated by look pushed tag in the tagList. if not found return
        if (opts.tagList) {
          var $tagList = opts.tagList;

          // change each array item to lower case
          $.each($tagList, function (index, item) {
            $tagList[index] = item.toLowerCase();
          });
          var suggestion = $.inArray(tag.toLowerCase(), $tagList);

          if (-1 === suggestion) {
            //console.log("tag:" + tag + " not in tagList, not adding it");
            return;
          }
        }

      }

      if (opts.CapitalizeFirstLetter && tag.length > 1) {
        tag = tag.charAt(0).toUpperCase() + tag.slice(1).toLowerCase();
      }

      // call the validator (if any) and do not let the tag pass if invalid
      if (opts.validator && !opts.validator(tag)) {
        $self.trigger('tm:invalid', tag)
        return;
      }

      // dont accept new tags beyond the defined maximum
      if (opts.maxTags > 0 && tlis.length >= opts.maxTags) { return; }

      alreadyInList = false;
      //use jQuery.map to make this work in IE8 (pure JS map is JS 1.6 but IE8 only supports JS 1.5)
      tlisLowerCase = jQuery.map(tlis, function (elem) {
        return elem.toLowerCase();
      });

      idx = $.inArray(tag.toLowerCase(), tlisLowerCase);

      if (-1 !== idx) {
        // console.log("tag:" + tag + " !!already in list!!");
        alreadyInList = true;
      }

      if (alreadyInList) {
        $self.trigger('tm:duplicated', tag);
        if (opts.blinkClass) {
          for (var i = 0; i < 6; ++i) {
            $("#" + $self.data("tm_rndid") + "_" + tlid[idx]).queue(function (next) {
              $(this).toggleClass(opts.blinkClass);
              next();
            }).delay(100);
          }
        } else {
          $("#" + $self.data("tm_rndid") + "_" + tlid[idx]).stop()
              .animate({ backgroundColor: opts.blinkBGColor_1 }, 100)
              .animate({ backgroundColor: opts.blinkBGColor_2 }, 100)
              .animate({ backgroundColor: opts.blinkBGColor_1 }, 100)
              .animate({ backgroundColor: opts.blinkBGColor_2 }, 100)
              .animate({ backgroundColor: opts.blinkBGColor_1 }, 100)
              .animate({ backgroundColor: opts.blinkBGColor_2 }, 100);
        }
      } else {
        if (opts.externalTagId === true) {
          if (externalTagId === undefined) {
            $.error('externalTagId is not passed for tag -' + tag);
          }
          tagId = externalTagId;
        } else {
          max = Math.max.apply(null, tlid);
          max = max === -Infinity ? 0 : max;

          tagId = ++max;
        }
        if (!ignoreEvents) { $self.trigger('tm:pushing', [tag, tagId]); }
        tlis.push(tag);
        tlid.push(tagId);

        if (!ignoreEvents)
          if (opts.AjaxPush !== null && opts.AjaxPushAllTags == null) {
            if ($.inArray(tag, opts.prefilled) === -1) {
              $.post(opts.AjaxPush, $.extend({ tag: tag }, opts.AjaxPushParameters));
            }
          }

        // console.log("tagList: " + tlis);

        newTagId = $self.data("tm_rndid") + '_' + tagId;
        newTagRemoveId = $self.data("tm_rndid") + '_Remover_' + tagId;
        escaped = $("<span/>").text(tag).html();

        html = '<span class="' + privateMethods.tagClasses.call($self) + '" id="' + newTagId + '">';
        html += '<span>' + escaped + '</span>';
        html += '<a href="#" class="tm-tag-remove" id="' + newTagRemoveId + '" TagIdToRemove="' + tagId + '">';
        html += opts.tagCloseIcon + '</a></span> ';
        $el = $(html);

        
        var typeAheadMess = $self.parents('.twitter-typeahead')[0] !== undefined;
        if (opts.tagsContainer !== null) {
          $(opts.tagsContainer).append($el);
        } else {
          if (tlid.length > 1) {
            if (typeAheadMess) {
              var lastTagId = $self.data("tm_rndid") + '_' + --tagId;
              jQuery('#' + lastTagId).after($el);
            } else {
              lastTagObj = $self.siblings("#" + $self.data("tm_rndid") + "_" + tlid[tlid.length - 2]);
              lastTagObj.after($el);
            }
          } else {
            if (typeAheadMess) {
              $self.parents('.twitter-typeahead').before($el);
            } else {
              $self.before($el);
            }
          }
        }

        $el.find("#" + newTagRemoveId).on("click", $self, function (e) {
          e.preventDefault();
          var TagIdToRemove = parseInt($(this).attr("TagIdToRemove"));
          privateMethods.spliceTag.call($self, TagIdToRemove, e.data);
        });

        privateMethods.refreshHiddenTagList.call($self);

        if (!ignoreEvents) { $self.trigger('tm:pushed', [tag, tagId]); }

        privateMethods.showOrHide.call($self);
        //if (tagManagerOptions.maxTags > 0 && tlis.length >= tagManagerOptions.maxTags) {
        //  obj.hide();
        //}
      }
      $self.val("");
    },

    popTag: function () {
      var $self = $(this), tagId, tagBeingRemoved,
      tlis = $self.data("tlis"),
      tlid = $self.data("tlid");

      if (tlid.length > 0) {
        tagId = tlid.pop();

        tagBeingRemoved = tlis[tlis.length - 1];
        $self.trigger('tm:popping', [tagBeingRemoved, tagId]);
        tlis.pop();

        // console.log("TagIdToRemove: " + tagId);
        $("#" + $self.data("tm_rndid") + "_" + tagId).remove();
        privateMethods.refreshHiddenTagList.call($self);
        $self.trigger('tm:popped', [tagBeingRemoved, tagId]);

        privateMethods.showOrHide.call($self);
        // console.log(tlis);
      }
    },

    empty: function () {
      var $self = $(this), tlis = $self.data("tlis"), tlid = $self.data("tlid"), tagId;

      while (tlid.length > 0) {
        tagId = tlid.pop();
        tlis.pop();
        // console.log("TagIdToRemove: " + tagId);
        $("#" + $self.data("tm_rndid") + "_" + tagId).remove();
        privateMethods.refreshHiddenTagList.call($self);
        // console.log(tlis);
      }
      $self.trigger('tm:emptied', null);

      privateMethods.showOrHide.call($self);
      //if (tagManagerOptions.maxTags > 0 && tlis.length < tagManagerOptions.maxTags) {
      //  obj.show();
      //}
    },

    tags: function () {
      var $self = this, tlis = $self.data("tlis");
      return tlis;
    }
  },

  privateMethods = {
    showOrHide: function () {
      var $self = this, opts = $self.data('opts'), tlis = $self.data("tlis");

      if (opts.maxTags > 0 && tlis.length < opts.maxTags) {
        $self.show();
        $self.trigger('tm:show');
      }

      if (opts.maxTags > 0 && tlis.length >= opts.maxTags) {
        $self.hide();
        $self.trigger('tm:hide');
      }
    },

    tagClasses: function () {
      var $self = $(this), opts = $self.data('opts'), tagBaseClass = opts.tagBaseClass,
      inputBaseClass = opts.inputBaseClass, cl;
      // 1) default class (tm-tag)
      cl = tagBaseClass;
      // 2) interpolate from input class: tm-input-xxx --> tm-tag-xxx
      if ($self.attr('class')) {
        $.each($self.attr('class').split(' '), function (index, value) {
          if (value.indexOf(inputBaseClass + '-') !== -1) {
            cl += ' ' + tagBaseClass + value.substring(inputBaseClass.length);
          }
        });
      }
      // 3) tags from tagClass option
      cl += (opts.tagClass ? ' ' + opts.tagClass : '');
      return cl;
    },

    trimTag: function (tag, delimiterChars) {
      var i;
      tag = $.trim(tag);
      // truncate at the first delimiter char
      i = 0;
      for (i; i < tag.length; i++) {
        if ($.inArray(tag.charCodeAt(i), delimiterChars) !== -1) { break; }
      }
      return tag.substring(0, i);
    },

    refreshHiddenTagList: function () {
      var $self = $(this), tlis = $self.data("tlis"), lhiddenTagList = $self.data("lhiddenTagList");

      if (lhiddenTagList) {
        $(lhiddenTagList).val(tlis.join($self.data('opts').baseDelimiter)).change();
      }

      $self.trigger('tm:refresh', tlis.join($self.data('opts').baseDelimiter));
    },

    killEvent: function (e) {
      e.cancelBubble = true;
      e.returnValue = false;
      e.stopPropagation();
      e.preventDefault();
    },

    keyInArray: function (e, ary) {
      return $.inArray(e.which, ary) !== -1;
    },

    applyDelimiter: function (e) {
      var $self = $(this);
      publicMethods.pushTag.call($self, $(this).val());
      e.preventDefault();
    },

    prefill: function (pta) {
      var $self = $(this);
      var opts = $self.data('opts')
      $.each(pta, function (key, val) {
        if (opts.externalTagId === true) {
          publicMethods.pushTag.call($self, val[opts.prefillValueFieldName], true, val[opts.prefillIdFieldName]);
        } else {
          publicMethods.pushTag.call($self, val, true);
        }
      });
    },

    pushAllTags: function (e, tag) {
      var $self = $(this), opts = $self.data('opts'), tlis = $self.data("tlis");
      if (opts.AjaxPushAllTags) {
        if (e.type !== 'tm:pushed' || $.inArray(tag, opts.prefilled) === -1) {
          $.post(opts.AjaxPush, $.extend({ tags: tlis.join(opts.baseDelimiter) }, opts.AjaxPushParameters));
        }
      }
    },

    spliceTag: function (tagId) {
      var $self = this, tlis = $self.data("tlis"), tlid = $self.data("tlid"), idx = $.inArray(tagId, tlid),
      tagBeingRemoved;

      // console.log("TagIdToRemove: " + tagId);
      // console.log("position: " + idx);

      if (-1 !== idx) {
        tagBeingRemoved = tlis[idx];
        $self.trigger('tm:splicing', [tagBeingRemoved, tagId]);
        $("#" + $self.data("tm_rndid") + "_" + tagId).remove();
        tlis.splice(idx, 1);
        tlid.splice(idx, 1);
        privateMethods.refreshHiddenTagList.call($self);
        $self.trigger('tm:spliced', [tagBeingRemoved, tagId]);
        // console.log(tlis);
      }

      privateMethods.showOrHide.call($self);
      //if (tagManagerOptions.maxTags > 0 && tlis.length < tagManagerOptions.maxTags) {
      //  obj.show();
      //}
    },

    init: function (options) {
      var opts = $.extend({}, defaults, options), delimiters, keyNums;

      opts.hiddenTagListName = (opts.hiddenTagListName === null)
          ? 'hidden-' + this.attr('name')
          : opts.hiddenTagListName;

      delimiters = opts.delimeters || opts.delimiters; // 'delimeter' is deprecated
      keyNums = [9, 13, 17, 18, 19, 37, 38, 39, 40]; // delimiter values to be handled as key codes
      opts.delimiterChars = [];
      opts.delimiterKeys = [];

      $.each(delimiters, function (i, v) {
        if ($.inArray(v, keyNums) !== -1) {
          opts.delimiterKeys.push(v);
        } else {
          opts.delimiterChars.push(v);
        }
      });

      opts.baseDelimiter = String.fromCharCode(opts.delimiterChars[0] || 44);
      opts.tagBaseClass = 'tm-tag';
      opts.inputBaseClass = 'tm-input';

      if (!$.isFunction(opts.validator)) { opts.validator = null; }

      this.each(function () {
        var $self = $(this), hiddenObj = '', rndid = '', albet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

        // prevent double-initialization of TagManager
        if ($self.data('tagManager')) { return false; }
        $self.data('tagManager', true);

        for (var i = 0; i < 5; i++) {
          rndid += albet.charAt(Math.floor(Math.random() * albet.length));
        }

        $self.data("tm_rndid", rndid);

        // store instance-specific data in the DOM object
        $self.data('opts', opts)
            .data('tlis', []) //list of string tags
            .data('tlid', []); //list of ID of the string tags

        if (opts.output === null) {
          hiddenObj = $('<input/>', {
            type: 'hidden',
            name: opts.hiddenTagListName
          });
          $self.after(hiddenObj);
          $self.data("lhiddenTagList", hiddenObj);
        } else {
          $self.data("lhiddenTagList", $(opts.output));
        }

        if (opts.AjaxPushAllTags) {
          $self.on('tm:spliced', privateMethods.pushAllTags);
          $self.on('tm:popped', privateMethods.pushAllTags);
          $self.on('tm:pushed', privateMethods.pushAllTags);
        }

        // hide popovers on focus and keypress events
        $self.on('focus keypress', function (e) {
          if ($(this).popover) { $(this).popover('hide'); }
        });

        // handle ESC (keyup used for browser compatibility)
        if (opts.isClearInputOnEsc) {
          $self.on('keyup', function (e) {
            if (e.which === 27) {
              // console.log('esc detected');
              $(this).val('');
              privateMethods.killEvent(e);
            }
          });
        }

        $self.on('keypress', function (e) {
          // push ASCII-based delimiters
          if (privateMethods.keyInArray(e, opts.delimiterChars)) {
            privateMethods.applyDelimiter.call($self, e);
          }
        });

        $self.on('keydown', function (e) {
          // disable ENTER
          if (e.which === 13) {
            if (opts.preventSubmitOnEnter) {
              privateMethods.killEvent(e);
            }
          }

          // push key-based delimiters (includes <enter> by default)
          if (privateMethods.keyInArray(e, opts.delimiterKeys)) {
            privateMethods.applyDelimiter.call($self, e);
          }
        });

        // BACKSPACE (keydown used for browser compatibility)
        if (opts.deleteTagsOnBackspace) {
          $self.on('keydown', function (e) {
            if (privateMethods.keyInArray(e, opts.backspace)) {
              // console.log("backspace detected");
              if ($(this).val().length <= 0) {
                publicMethods.popTag.call($self);
                privateMethods.killEvent(e);
              }
            }
          });
        }

        // on tag pop fill back the tag's content to the input field
        if (opts.fillInputOnTagRemove) {
          $self.on('tm:popped', function (e, tag) {
            $(this).val(tag);
          });
        }

        $self.change(function (e) {
          if (!/webkit/.test(navigator.userAgent.toLowerCase())) {
            $self.focus();
          } // why?

          /* unimplemented mode to push tag on blur
           else if (tagManagerOptions.pushTagOnBlur) {
           console.log('change: pushTagOnBlur ' + tag);
           pushTag($(this).val());
           } */
          privateMethods.killEvent(e);
        });

        if (opts.prefilled !== null) {
          if (typeof (opts.prefilled) === "object") {
            privateMethods.prefill.call($self, opts.prefilled);
          } else if (typeof (opts.prefilled) === "string") {
            privateMethods.prefill.call($self, opts.prefilled.split(opts.baseDelimiter));
          } else if (typeof (opts.prefilled) === "function") {
            privateMethods.prefill.call($self, opts.prefilled());
          }
        } else if (opts.output !== null) {
          if ($(opts.output) && $(opts.output).val()) { var existing_tags = $(opts.output); }
          privateMethods.prefill.call($self, $(opts.output).val().split(opts.baseDelimiter));
        }

      });

      return this;
    }
  };

  $.fn.tagsManager = function (method) {
    var $self = $(this);

    if (!(0 in this)) { return this; }

    if (publicMethods[method]) {
      return publicMethods[method].apply($self, Array.prototype.slice.call(arguments, 1));
    } else if (typeof method === 'object' || !method) {
      return privateMethods.init.apply(this, arguments);
    } else {
      $.error('Method ' + method + ' does not exist.');
      return false;
    }
  };

}(jQuery));


//------------------------------------------------------
/*!
 * typeahead.js 0.11.1
 * https://github.com/twitter/typeahead.js
 * Copyright 2013-2015 Twitter, Inc. and other contributors; Licensed MIT
 */

(function(root, factory) {
    if (typeof define === "function" && define.amd) {
        define("bloodhound", [ "jquery" ], function(a0) {
            return root["Bloodhound"] = factory(a0);
        });
    } else if (typeof exports === "object") {
        module.exports = factory(require("jquery"));
    } else {
        root["Bloodhound"] = factory(jQuery);
    }
})(this, function($) {
    var _ = function() {
        "use strict";
        return {
            isMsie: function() {
                return /(msie|trident)/i.test(navigator.userAgent) ? navigator.userAgent.match(/(msie |rv:)(\d+(.\d+)?)/i)[2] : false;
            },
            isBlankString: function(str) {
                return !str || /^\s*$/.test(str);
            },
            escapeRegExChars: function(str) {
                return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
            },
            isString: function(obj) {
                return typeof obj === "string";
            },
            isNumber: function(obj) {
                return typeof obj === "number";
            },
            isArray: $.isArray,
            isFunction: $.isFunction,
            isObject: $.isPlainObject,
            isUndefined: function(obj) {
                return typeof obj === "undefined";
            },
            isElement: function(obj) {
                return !!(obj && obj.nodeType === 1);
            },
            isJQuery: function(obj) {
                return obj instanceof $;
            },
            toStr: function toStr(s) {
                return _.isUndefined(s) || s === null ? "" : s + "";
            },
            bind: $.proxy,
            each: function(collection, cb) {
                $.each(collection, reverseArgs);
                function reverseArgs(index, value) {
                    return cb(value, index);
                }
            },
            map: $.map,
            filter: $.grep,
            every: function(obj, test) {
                var result = true;
                if (!obj) {
                    return result;
                }
                $.each(obj, function(key, val) {
                    if (!(result = test.call(null, val, key, obj))) {
                        return false;
                    }
                });
                return !!result;
            },
            some: function(obj, test) {
                var result = false;
                if (!obj) {
                    return result;
                }
                $.each(obj, function(key, val) {
                    if (result = test.call(null, val, key, obj)) {
                        return false;
                    }
                });
                return !!result;
            },
            mixin: $.extend,
            identity: function(x) {
                return x;
            },
            clone: function(obj) {
                return $.extend(true, {}, obj);
            },
            getIdGenerator: function() {
                var counter = 0;
                return function() {
                    return counter++;
                };
            },
            templatify: function templatify(obj) {
                return $.isFunction(obj) ? obj : template;
                function template() {
                    return String(obj);
                }
            },
            defer: function(fn) {
                setTimeout(fn, 0);
            },
            debounce: function(func, wait, immediate) {
                var timeout, result;
                return function() {
                    var context = this, args = arguments, later, callNow;
                    later = function() {
                        timeout = null;
                        if (!immediate) {
                            result = func.apply(context, args);
                        }
                    };
                    callNow = immediate && !timeout;
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                    if (callNow) {
                        result = func.apply(context, args);
                    }
                    return result;
                };
            },
            throttle: function(func, wait) {
                var context, args, timeout, result, previous, later;
                previous = 0;
                later = function() {
                    previous = new Date();
                    timeout = null;
                    result = func.apply(context, args);
                };
                return function() {
                    var now = new Date(), remaining = wait - (now - previous);
                    context = this;
                    args = arguments;
                    if (remaining <= 0) {
                        clearTimeout(timeout);
                        timeout = null;
                        previous = now;
                        result = func.apply(context, args);
                    } else if (!timeout) {
                        timeout = setTimeout(later, remaining);
                    }
                    return result;
                };
            },
            stringify: function(val) {
                return _.isString(val) ? val : JSON.stringify(val);
            },
            noop: function() {}
        };
    }();
    var VERSION = "0.11.1";
    var tokenizers = function() {
        "use strict";
        return {
            nonword: nonword,
            whitespace: whitespace,
            obj: {
                nonword: getObjTokenizer(nonword),
                whitespace: getObjTokenizer(whitespace)
            }
        };
        function whitespace(str) {
            str = _.toStr(str);
            return str ? str.split(/\s+/) : [];
        }
        function nonword(str) {
            str = _.toStr(str);
            return str ? str.split(/\W+/) : [];
        }
        function getObjTokenizer(tokenizer) {
            return function setKey(keys) {
                keys = _.isArray(keys) ? keys : [].slice.call(arguments, 0);
                return function tokenize(o) {
                    var tokens = [];
                    _.each(keys, function(k) {
                        tokens = tokens.concat(tokenizer(_.toStr(o[k])));
                    });
                    return tokens;
                };
            };
        }
    }();
    var LruCache = function() {
        "use strict";
        function LruCache(maxSize) {
            this.maxSize = _.isNumber(maxSize) ? maxSize : 100;
            this.reset();
            if (this.maxSize <= 0) {
                this.set = this.get = $.noop;
            }
        }
        _.mixin(LruCache.prototype, {
            set: function set(key, val) {
                var tailItem = this.list.tail, node;
                if (this.size >= this.maxSize) {
                    this.list.remove(tailItem);
                    delete this.hash[tailItem.key];
                    this.size--;
                }
                if (node = this.hash[key]) {
                    node.val = val;
                    this.list.moveToFront(node);
                } else {
                    node = new Node(key, val);
                    this.list.add(node);
                    this.hash[key] = node;
                    this.size++;
                }
            },
            get: function get(key) {
                var node = this.hash[key];
                if (node) {
                    this.list.moveToFront(node);
                    return node.val;
                }
            },
            reset: function reset() {
                this.size = 0;
                this.hash = {};
                this.list = new List();
            }
        });
        function List() {
            this.head = this.tail = null;
        }
        _.mixin(List.prototype, {
            add: function add(node) {
                if (this.head) {
                    node.next = this.head;
                    this.head.prev = node;
                }
                this.head = node;
                this.tail = this.tail || node;
            },
            remove: function remove(node) {
                node.prev ? node.prev.next = node.next : this.head = node.next;
                node.next ? node.next.prev = node.prev : this.tail = node.prev;
            },
            moveToFront: function(node) {
                this.remove(node);
                this.add(node);
            }
        });
        function Node(key, val) {
            this.key = key;
            this.val = val;
            this.prev = this.next = null;
        }
        return LruCache;
    }();
    var PersistentStorage = function() {
        "use strict";
        var LOCAL_STORAGE;
        try {
            LOCAL_STORAGE = window.localStorage;
            LOCAL_STORAGE.setItem("~~~", "!");
            LOCAL_STORAGE.removeItem("~~~");
        } catch (err) {
            LOCAL_STORAGE = null;
        }
        function PersistentStorage(namespace, override) {
            this.prefix = [ "__", namespace, "__" ].join("");
            this.ttlKey = "__ttl__";
            this.keyMatcher = new RegExp("^" + _.escapeRegExChars(this.prefix));
            this.ls = override || LOCAL_STORAGE;
            !this.ls && this._noop();
        }
        _.mixin(PersistentStorage.prototype, {
            _prefix: function(key) {
                return this.prefix + key;
            },
            _ttlKey: function(key) {
                return this._prefix(key) + this.ttlKey;
            },
            _noop: function() {
                this.get = this.set = this.remove = this.clear = this.isExpired = _.noop;
            },
            _safeSet: function(key, val) {
                try {
                    this.ls.setItem(key, val);
                } catch (err) {
                    if (err.name === "QuotaExceededError") {
                        this.clear();
                        this._noop();
                    }
                }
            },
            get: function(key) {
                if (this.isExpired(key)) {
                    this.remove(key);
                }
                return decode(this.ls.getItem(this._prefix(key)));
            },
            set: function(key, val, ttl) {
                if (_.isNumber(ttl)) {
                    this._safeSet(this._ttlKey(key), encode(now() + ttl));
                } else {
                    this.ls.removeItem(this._ttlKey(key));
                }
                return this._safeSet(this._prefix(key), encode(val));
            },
            remove: function(key) {
                this.ls.removeItem(this._ttlKey(key));
                this.ls.removeItem(this._prefix(key));
                return this;
            },
            clear: function() {
                var i, keys = gatherMatchingKeys(this.keyMatcher);
                for (i = keys.length; i--; ) {
                    this.remove(keys[i]);
                }
                return this;
            },
            isExpired: function(key) {
                var ttl = decode(this.ls.getItem(this._ttlKey(key)));
                return _.isNumber(ttl) && now() > ttl ? true : false;
            }
        });
        return PersistentStorage;
        function now() {
            return new Date().getTime();
        }
        function encode(val) {
            return JSON.stringify(_.isUndefined(val) ? null : val);
        }
        function decode(val) {
            return $.parseJSON(val);
        }
        function gatherMatchingKeys(keyMatcher) {
            var i, key, keys = [], len = LOCAL_STORAGE.length;
            for (i = 0; i < len; i++) {
                if ((key = LOCAL_STORAGE.key(i)).match(keyMatcher)) {
                    keys.push(key.replace(keyMatcher, ""));
                }
            }
            return keys;
        }
    }();
    var Transport = function() {
        "use strict";
        var pendingRequestsCount = 0, pendingRequests = {}, maxPendingRequests = 6, sharedCache = new LruCache(10);
        function Transport(o) {
            o = o || {};
            this.cancelled = false;
            this.lastReq = null;
            this._send = o.transport;
            this._get = o.limiter ? o.limiter(this._get) : this._get;
            this._cache = o.cache === false ? new LruCache(0) : sharedCache;
        }
        Transport.setMaxPendingRequests = function setMaxPendingRequests(num) {
            maxPendingRequests = num;
        };
        Transport.resetCache = function resetCache() {
            sharedCache.reset();
        };
        _.mixin(Transport.prototype, {
            _fingerprint: function fingerprint(o) {
                o = o || {};
                return o.url + o.type + $.param(o.data || {});
            },
            _get: function(o, cb) {
                var that = this, fingerprint, jqXhr;
                fingerprint = this._fingerprint(o);
                if (this.cancelled || fingerprint !== this.lastReq) {
                    return;
                }
                if (jqXhr = pendingRequests[fingerprint]) {
                    jqXhr.done(done).fail(fail);
                } else if (pendingRequestsCount < maxPendingRequests) {
                    pendingRequestsCount++;
                    pendingRequests[fingerprint] = this._send(o).done(done).fail(fail).always(always);
                } else {
                    this.onDeckRequestArgs = [].slice.call(arguments, 0);
                }
                function done(resp) {
                    cb(null, resp);
                    that._cache.set(fingerprint, resp);
                }
                function fail() {
                    cb(true);
                }
                function always() {
                    pendingRequestsCount--;
                    delete pendingRequests[fingerprint];
                    if (that.onDeckRequestArgs) {
                        that._get.apply(that, that.onDeckRequestArgs);
                        that.onDeckRequestArgs = null;
                    }
                }
            },
            get: function(o, cb) {
                var resp, fingerprint;
                cb = cb || $.noop;
                o = _.isString(o) ? {
                    url: o
                } : o || {};
                fingerprint = this._fingerprint(o);
                this.cancelled = false;
                this.lastReq = fingerprint;
                if (resp = this._cache.get(fingerprint)) {
                    cb(null, resp);
                } else {
                    this._get(o, cb);
                }
            },
            cancel: function() {
                this.cancelled = true;
            }
        });
        return Transport;
    }();
    var SearchIndex = window.SearchIndex = function() {
        "use strict";
        var CHILDREN = "c", IDS = "i";
        function SearchIndex(o) {
            o = o || {};
            if (!o.datumTokenizer || !o.queryTokenizer) {
                $.error("datumTokenizer and queryTokenizer are both required");
            }
            this.identify = o.identify || _.stringify;
            this.datumTokenizer = o.datumTokenizer;
            this.queryTokenizer = o.queryTokenizer;
            this.reset();
        }
        _.mixin(SearchIndex.prototype, {
            bootstrap: function bootstrap(o) {
                this.datums = o.datums;
                this.trie = o.trie;
            },
            add: function(data) {
                var that = this;
                data = _.isArray(data) ? data : [ data ];
                _.each(data, function(datum) {
                    var id, tokens;
                    that.datums[id = that.identify(datum)] = datum;
                    tokens = normalizeTokens(that.datumTokenizer(datum));
                    _.each(tokens, function(token) {
                        var node, chars, ch;
                        node = that.trie;
                        chars = token.split("");
                        while (ch = chars.shift()) {
                            node = node[CHILDREN][ch] || (node[CHILDREN][ch] = newNode());
                            node[IDS].push(id);
                        }
                    });
                });
            },
            get: function get(ids) {
                var that = this;
                return _.map(ids, function(id) {
                    return that.datums[id];
                });
            },
            search: function search(query) {
                var that = this, tokens, matches;
                tokens = normalizeTokens(this.queryTokenizer(query));
                _.each(tokens, function(token) {
                    var node, chars, ch, ids;
                    if (matches && matches.length === 0) {
                        return false;
                    }
                    node = that.trie;
                    chars = token.split("");
                    while (node && (ch = chars.shift())) {
                        node = node[CHILDREN][ch];
                    }
                    if (node && chars.length === 0) {
                        ids = node[IDS].slice(0);
                        matches = matches ? getIntersection(matches, ids) : ids;
                    } else {
                        matches = [];
                        return false;
                    }
                });
                return matches ? _.map(unique(matches), function(id) {
                    return that.datums[id];
                }) : [];
            },
            all: function all() {
                var values = [];
                for (var key in this.datums) {
                    values.push(this.datums[key]);
                }
                return values;
            },
            reset: function reset() {
                this.datums = {};
                this.trie = newNode();
            },
            serialize: function serialize() {
                return {
                    datums: this.datums,
                    trie: this.trie
                };
            }
        });
        return SearchIndex;
        function normalizeTokens(tokens) {
            tokens = _.filter(tokens, function(token) {
                return !!token;
            });
            tokens = _.map(tokens, function(token) {
                return token.toLowerCase();
            });
            return tokens;
        }
        function newNode() {
            var node = {};
            node[IDS] = [];
            node[CHILDREN] = {};
            return node;
        }
        function unique(array) {
            var seen = {}, uniques = [];
            for (var i = 0, len = array.length; i < len; i++) {
                if (!seen[array[i]]) {
                    seen[array[i]] = true;
                    uniques.push(array[i]);
                }
            }
            return uniques;
        }
        function getIntersection(arrayA, arrayB) {
            var ai = 0, bi = 0, intersection = [];
            arrayA = arrayA.sort();
            arrayB = arrayB.sort();
            var lenArrayA = arrayA.length, lenArrayB = arrayB.length;
            while (ai < lenArrayA && bi < lenArrayB) {
                if (arrayA[ai] < arrayB[bi]) {
                    ai++;
                } else if (arrayA[ai] > arrayB[bi]) {
                    bi++;
                } else {
                    intersection.push(arrayA[ai]);
                    ai++;
                    bi++;
                }
            }
            return intersection;
        }
    }();
    var Prefetch = function() {
        "use strict";
        var keys;
        keys = {
            data: "data",
            protocol: "protocol",
            thumbprint: "thumbprint"
        };
        function Prefetch(o) {
            this.url = o.url;
            this.ttl = o.ttl;
            this.cache = o.cache;
            this.prepare = o.prepare;
            this.transform = o.transform;
            this.transport = o.transport;
            this.thumbprint = o.thumbprint;
            this.storage = new PersistentStorage(o.cacheKey);
        }
        _.mixin(Prefetch.prototype, {
            _settings: function settings() {
                return {
                    url: this.url,
                    type: "GET",
                    dataType: "json"
                };
            },
            store: function store(data) {
                if (!this.cache) {
                    return;
                }
                this.storage.set(keys.data, data, this.ttl);
                this.storage.set(keys.protocol, location.protocol, this.ttl);
                this.storage.set(keys.thumbprint, this.thumbprint, this.ttl);
            },
            fromCache: function fromCache() {
                var stored = {}, isExpired;
                if (!this.cache) {
                    return null;
                }
                stored.data = this.storage.get(keys.data);
                stored.protocol = this.storage.get(keys.protocol);
                stored.thumbprint = this.storage.get(keys.thumbprint);
                isExpired = stored.thumbprint !== this.thumbprint || stored.protocol !== location.protocol;
                return stored.data && !isExpired ? stored.data : null;
            },
            fromNetwork: function(cb) {
                var that = this, settings;
                if (!cb) {
                    return;
                }
                settings = this.prepare(this._settings());
                this.transport(settings).fail(onError).done(onResponse);
                function onError() {
                    cb(true);
                }
                function onResponse(resp) {
                    cb(null, that.transform(resp));
                }
            },
            clear: function clear() {
                this.storage.clear();
                return this;
            }
        });
        return Prefetch;
    }();
    var Remote = function() {
        "use strict";
        function Remote(o) {
            this.url = o.url;
            this.prepare = o.prepare;
            this.transform = o.transform;
            this.transport = new Transport({
                cache: o.cache,
                limiter: o.limiter,
                transport: o.transport
            });
        }
        _.mixin(Remote.prototype, {
            _settings: function settings() {
                return {
                    url: this.url,
                    type: "GET",
                    dataType: "json"
                };
            },
            get: function get(query, cb) {
                var that = this, settings;
                if (!cb) {
                    return;
                }
                query = query || "";
                settings = this.prepare(query, this._settings());
                return this.transport.get(settings, onResponse);
                function onResponse(err, resp) {
                    err ? cb([]) : cb(that.transform(resp));
                }
            },
            cancelLastRequest: function cancelLastRequest() {
                this.transport.cancel();
            }
        });
        return Remote;
    }();
    var oParser = function() {
        "use strict";
        return function parse(o) {
            var defaults, sorter;
            defaults = {
                initialize: true,
                identify: _.stringify,
                datumTokenizer: null,
                queryTokenizer: null,
                sufficient: 5,
                sorter: null,
                local: [],
                prefetch: null,
                remote: null
            };
            o = _.mixin(defaults, o || {});
            !o.datumTokenizer && $.error("datumTokenizer is required");
            !o.queryTokenizer && $.error("queryTokenizer is required");
            sorter = o.sorter;
            o.sorter = sorter ? function(x) {
                return x.sort(sorter);
            } : _.identity;
            o.local = _.isFunction(o.local) ? o.local() : o.local;
            o.prefetch = parsePrefetch(o.prefetch);
            o.remote = parseRemote(o.remote);
            return o;
        };
        function parsePrefetch(o) {
            var defaults;
            if (!o) {
                return null;
            }
            defaults = {
                url: null,
                ttl: 24 * 60 * 60 * 1e3,
                cache: true,
                cacheKey: null,
                thumbprint: "",
                prepare: _.identity,
                transform: _.identity,
                transport: null
            };
            o = _.isString(o) ? {
                url: o
            } : o;
            o = _.mixin(defaults, o);
            !o.url && $.error("prefetch requires url to be set");
            o.transform = o.filter || o.transform;
            o.cacheKey = o.cacheKey || o.url;
            o.thumbprint = VERSION + o.thumbprint;
            o.transport = o.transport ? callbackToDeferred(o.transport) : $.ajax;
            return o;
        }
        function parseRemote(o) {
            var defaults;
            if (!o) {
                return;
            }
            defaults = {
                url: null,
                cache: true,
                prepare: null,
                replace: null,
                wildcard: null,
                limiter: null,
                rateLimitBy: "debounce",
                rateLimitWait: 300,
                transform: _.identity,
                transport: null
            };
            o = _.isString(o) ? {
                url: o
            } : o;
            o = _.mixin(defaults, o);
            !o.url && $.error("remote requires url to be set");
            o.transform = o.filter || o.transform;
            o.prepare = toRemotePrepare(o);
            o.limiter = toLimiter(o);
            o.transport = o.transport ? callbackToDeferred(o.transport) : $.ajax;
            delete o.replace;
            delete o.wildcard;
            delete o.rateLimitBy;
            delete o.rateLimitWait;
            return o;
        }
        function toRemotePrepare(o) {
            var prepare, replace, wildcard;
            prepare = o.prepare;
            replace = o.replace;
            wildcard = o.wildcard;
            if (prepare) {
                return prepare;
            }
            if (replace) {
                prepare = prepareByReplace;
            } else if (o.wildcard) {
                prepare = prepareByWildcard;
            } else {
                prepare = idenityPrepare;
            }
            return prepare;
            function prepareByReplace(query, settings) {
                settings.url = replace(settings.url, query);
                return settings;
            }
            function prepareByWildcard(query, settings) {
                settings.url = settings.url.replace(wildcard, encodeURIComponent(query));
                return settings;
            }
            function idenityPrepare(query, settings) {
                return settings;
            }
        }
        function toLimiter(o) {
            var limiter, method, wait;
            limiter = o.limiter;
            method = o.rateLimitBy;
            wait = o.rateLimitWait;
            if (!limiter) {
                limiter = /^throttle$/i.test(method) ? throttle(wait) : debounce(wait);
            }
            return limiter;
            function debounce(wait) {
                return function debounce(fn) {
                    return _.debounce(fn, wait);
                };
            }
            function throttle(wait) {
                return function throttle(fn) {
                    return _.throttle(fn, wait);
                };
            }
        }
        function callbackToDeferred(fn) {
            return function wrapper(o) {
                var deferred = $.Deferred();
                fn(o, onSuccess, onError);
                return deferred;
                function onSuccess(resp) {
                    _.defer(function() {
                        deferred.resolve(resp);
                    });
                }
                function onError(err) {
                    _.defer(function() {
                        deferred.reject(err);
                    });
                }
            };
        }
    }();
    var Bloodhound = function() {
        "use strict";
        var old;
        old = window && window.Bloodhound;
        function Bloodhound(o) {
            o = oParser(o);
            this.sorter = o.sorter;
            this.identify = o.identify;
            this.sufficient = o.sufficient;
            this.local = o.local;
            this.remote = o.remote ? new Remote(o.remote) : null;
            this.prefetch = o.prefetch ? new Prefetch(o.prefetch) : null;
            this.index = new SearchIndex({
                identify: this.identify,
                datumTokenizer: o.datumTokenizer,
                queryTokenizer: o.queryTokenizer
            });
            o.initialize !== false && this.initialize();
        }
        Bloodhound.noConflict = function noConflict() {
            window && (window.Bloodhound = old);
            return Bloodhound;
        };
        Bloodhound.tokenizers = tokenizers;
        _.mixin(Bloodhound.prototype, {
            __ttAdapter: function ttAdapter() {
                var that = this;
                return this.remote ? withAsync : withoutAsync;
                function withAsync(query, sync, async) {
                    return that.search(query, sync, async);
                }
                function withoutAsync(query, sync) {
                    return that.search(query, sync);
                }
            },
            _loadPrefetch: function loadPrefetch() {
                var that = this, deferred, serialized;
                deferred = $.Deferred();
                if (!this.prefetch) {
                    deferred.resolve();
                } else if (serialized = this.prefetch.fromCache()) {
                    this.index.bootstrap(serialized);
                    deferred.resolve();
                } else {
                    this.prefetch.fromNetwork(done);
                }
                return deferred.promise();
                function done(err, data) {
                    if (err) {
                        return deferred.reject();
                    }
                    that.add(data);
                    that.prefetch.store(that.index.serialize());
                    deferred.resolve();
                }
            },
            _initialize: function initialize() {
                var that = this, deferred;
                this.clear();
                (this.initPromise = this._loadPrefetch()).done(addLocalToIndex);
                return this.initPromise;
                function addLocalToIndex() {
                    that.add(that.local);
                }
            },
            initialize: function initialize(force) {
                return !this.initPromise || force ? this._initialize() : this.initPromise;
            },
            add: function add(data) {
                this.index.add(data);
                return this;
            },
            get: function get(ids) {
                ids = _.isArray(ids) ? ids : [].slice.call(arguments);
                return this.index.get(ids);
            },
            search: function search(query, sync, async) {
                var that = this, local;
                local = this.sorter(this.index.search(query));
                sync(this.remote ? local.slice() : local);
                if (this.remote && local.length < this.sufficient) {
                    this.remote.get(query, processRemote);
                } else if (this.remote) {
                    this.remote.cancelLastRequest();
                }
                return this;
                function processRemote(remote) {
                    var nonDuplicates = [];
                    _.each(remote, function(r) {
                        !_.some(local, function(l) {
                            return that.identify(r) === that.identify(l);
                        }) && nonDuplicates.push(r);
                    });
                    async && async(nonDuplicates);
                }
            },
            all: function all() {
                return this.index.all();
            },
            clear: function clear() {
                this.index.reset();
                return this;
            },
            clearPrefetchCache: function clearPrefetchCache() {
                this.prefetch && this.prefetch.clear();
                return this;
            },
            clearRemoteCache: function clearRemoteCache() {
                Transport.resetCache();
                return this;
            },
            ttAdapter: function ttAdapter() {
                return this.__ttAdapter();
            }
        });
        return Bloodhound;
    }();
    return Bloodhound;
});

(function(root, factory) {
    if (typeof define === "function" && define.amd) {
        define("typeahead.js", [ "jquery" ], function(a0) {
            return factory(a0);
        });
    } else if (typeof exports === "object") {
        module.exports = factory(require("jquery"));
    } else {
        factory(jQuery);
    }
})(this, function($) {
    var _ = function() {
        "use strict";
        return {
            isMsie: function() {
                return /(msie|trident)/i.test(navigator.userAgent) ? navigator.userAgent.match(/(msie |rv:)(\d+(.\d+)?)/i)[2] : false;
            },
            isBlankString: function(str) {
                return !str || /^\s*$/.test(str);
            },
            escapeRegExChars: function(str) {
                return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
            },
            isString: function(obj) {
                return typeof obj === "string";
            },
            isNumber: function(obj) {
                return typeof obj === "number";
            },
            isArray: $.isArray,
            isFunction: $.isFunction,
            isObject: $.isPlainObject,
            isUndefined: function(obj) {
                return typeof obj === "undefined";
            },
            isElement: function(obj) {
                return !!(obj && obj.nodeType === 1);
            },
            isJQuery: function(obj) {
                return obj instanceof $;
            },
            toStr: function toStr(s) {
                return _.isUndefined(s) || s === null ? "" : s + "";
            },
            bind: $.proxy,
            each: function(collection, cb) {
                $.each(collection, reverseArgs);
                function reverseArgs(index, value) {
                    return cb(value, index);
                }
            },
            map: $.map,
            filter: $.grep,
            every: function(obj, test) {
                var result = true;
                if (!obj) {
                    return result;
                }
                $.each(obj, function(key, val) {
                    if (!(result = test.call(null, val, key, obj))) {
                        return false;
                    }
                });
                return !!result;
            },
            some: function(obj, test) {
                var result = false;
                if (!obj) {
                    return result;
                }
                $.each(obj, function(key, val) {
                    if (result = test.call(null, val, key, obj)) {
                        return false;
                    }
                });
                return !!result;
            },
            mixin: $.extend,
            identity: function(x) {
                return x;
            },
            clone: function(obj) {
                return $.extend(true, {}, obj);
            },
            getIdGenerator: function() {
                var counter = 0;
                return function() {
                    return counter++;
                };
            },
            templatify: function templatify(obj) {
                return $.isFunction(obj) ? obj : template;
                function template() {
                    return String(obj);
                }
            },
            defer: function(fn) {
                setTimeout(fn, 0);
            },
            debounce: function(func, wait, immediate) {
                var timeout, result;
                return function() {
                    var context = this, args = arguments, later, callNow;
                    later = function() {
                        timeout = null;
                        if (!immediate) {
                            result = func.apply(context, args);
                        }
                    };
                    callNow = immediate && !timeout;
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                    if (callNow) {
                        result = func.apply(context, args);
                    }
                    return result;
                };
            },
            throttle: function(func, wait) {
                var context, args, timeout, result, previous, later;
                previous = 0;
                later = function() {
                    previous = new Date();
                    timeout = null;
                    result = func.apply(context, args);
                };
                return function() {
                    var now = new Date(), remaining = wait - (now - previous);
                    context = this;
                    args = arguments;
                    if (remaining <= 0) {
                        clearTimeout(timeout);
                        timeout = null;
                        previous = now;
                        result = func.apply(context, args);
                    } else if (!timeout) {
                        timeout = setTimeout(later, remaining);
                    }
                    return result;
                };
            },
            stringify: function(val) {
                return _.isString(val) ? val : JSON.stringify(val);
            },
            noop: function() {}
        };
    }();
    var WWW = function() {
        "use strict";
        var defaultClassNames = {
            wrapper: "twitter-typeahead",
            input: "tt-input",
            hint: "tt-hint",
            menu: "tt-menu",
            dataset: "tt-dataset",
            suggestion: "tt-suggestion",
            selectable: "tt-selectable",
            empty: "tt-empty",
            open: "tt-open",
            cursor: "tt-cursor",
            highlight: "tt-highlight"
        };
        return build;
        function build(o) {
            var www, classes;
            classes = _.mixin({}, defaultClassNames, o);
            www = {
                css: buildCss(),
                classes: classes,
                html: buildHtml(classes),
                selectors: buildSelectors(classes)
            };
            return {
                css: www.css,
                html: www.html,
                classes: www.classes,
                selectors: www.selectors,
                mixin: function(o) {
                    _.mixin(o, www);
                }
            };
        }
        function buildHtml(c) {
            return {
                wrapper: '<span class="' + c.wrapper + '"></span>',
                menu: '<div class="' + c.menu + '"></div>'
            };
        }
        function buildSelectors(classes) {
            var selectors = {};
            _.each(classes, function(v, k) {
                selectors[k] = "." + v;
            });
            return selectors;
        }
        function buildCss() {
            var css = {
                wrapper: {
                    position: "relative",
                    display: "inline-block"
                },
                hint: {
                    position: "absolute",
                    top: "0",
                    left: "0",
                    borderColor: "transparent",
                    boxShadow: "none",
                    opacity: "1"
                },
                input: {
                    position: "relative",
                    verticalAlign: "top",
                    backgroundColor: "transparent"
                },
                inputWithNoHint: {
                    position: "relative",
                    verticalAlign: "top"
                },
                menu: {
                    position: "absolute",
                    top: "100%",
                    left: "0",
                    zIndex: "100",
                    display: "none"
                },
                ltr: {
                    left: "0",
                    right: "auto"
                },
                rtl: {
                    left: "auto",
                    right: " 0"
                }
            };
            if (_.isMsie()) {
                _.mixin(css.input, {
                    backgroundImage: "url(data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7)"
                });
            }
            return css;
        }
    }();
    var EventBus = function() {
        "use strict";
        var namespace, deprecationMap;
        namespace = "typeahead:";
        deprecationMap = {
            render: "rendered",
            cursorchange: "cursorchanged",
            select: "selected",
            autocomplete: "autocompleted"
        };
        function EventBus(o) {
            if (!o || !o.el) {
                $.error("EventBus initialized without el");
            }
            this.$el = $(o.el);
        }
        _.mixin(EventBus.prototype, {
            _trigger: function(type, args) {
                var $e;
                $e = $.Event(namespace + type);
                (args = args || []).unshift($e);
                this.$el.trigger.apply(this.$el, args);
                return $e;
            },
            before: function(type) {
                var args, $e;
                args = [].slice.call(arguments, 1);
                $e = this._trigger("before" + type, args);
                return $e.isDefaultPrevented();
            },
            trigger: function(type) {
                var deprecatedType;
                this._trigger(type, [].slice.call(arguments, 1));
                if (deprecatedType = deprecationMap[type]) {
                    this._trigger(deprecatedType, [].slice.call(arguments, 1));
                }
            }
        });
        return EventBus;
    }();
    var EventEmitter = function() {
        "use strict";
        var splitter = /\s+/, nextTick = getNextTick();
        return {
            onSync: onSync,
            onAsync: onAsync,
            off: off,
            trigger: trigger
        };
        function on(method, types, cb, context) {
            var type;
            if (!cb) {
                return this;
            }
            types = types.split(splitter);
            cb = context ? bindContext(cb, context) : cb;
            this._callbacks = this._callbacks || {};
            while (type = types.shift()) {
                this._callbacks[type] = this._callbacks[type] || {
                    sync: [],
                    async: []
                };
                this._callbacks[type][method].push(cb);
            }
            return this;
        }
        function onAsync(types, cb, context) {
            return on.call(this, "async", types, cb, context);
        }
        function onSync(types, cb, context) {
            return on.call(this, "sync", types, cb, context);
        }
        function off(types) {
            var type;
            if (!this._callbacks) {
                return this;
            }
            types = types.split(splitter);
            while (type = types.shift()) {
                delete this._callbacks[type];
            }
            return this;
        }
        function trigger(types) {
            var type, callbacks, args, syncFlush, asyncFlush;
            if (!this._callbacks) {
                return this;
            }
            types = types.split(splitter);
            args = [].slice.call(arguments, 1);
            while ((type = types.shift()) && (callbacks = this._callbacks[type])) {
                syncFlush = getFlush(callbacks.sync, this, [ type ].concat(args));
                asyncFlush = getFlush(callbacks.async, this, [ type ].concat(args));
                syncFlush() && nextTick(asyncFlush);
            }
            return this;
        }
        function getFlush(callbacks, context, args) {
            return flush;
            function flush() {
                var cancelled;
                for (var i = 0, len = callbacks.length; !cancelled && i < len; i += 1) {
                    cancelled = callbacks[i].apply(context, args) === false;
                }
                return !cancelled;
            }
        }
        function getNextTick() {
            var nextTickFn;
            if (window.setImmediate) {
                nextTickFn = function nextTickSetImmediate(fn) {
                    setImmediate(function() {
                        fn();
                    });
                };
            } else {
                nextTickFn = function nextTickSetTimeout(fn) {
                    setTimeout(function() {
                        fn();
                    }, 0);
                };
            }
            return nextTickFn;
        }
        function bindContext(fn, context) {
            return fn.bind ? fn.bind(context) : function() {
                fn.apply(context, [].slice.call(arguments, 0));
            };
        }
    }();
    var highlight = function(doc) {
        "use strict";
        var defaults = {
            node: null,
            pattern: null,
            tagName: "strong",
            className: null,
            wordsOnly: false,
            caseSensitive: false
        };
        return function hightlight(o) {
            var regex;
            o = _.mixin({}, defaults, o);
            if (!o.node || !o.pattern) {
                return;
            }
            o.pattern = _.isArray(o.pattern) ? o.pattern : [ o.pattern ];
            regex = getRegex(o.pattern, o.caseSensitive, o.wordsOnly);
            traverse(o.node, hightlightTextNode);
            function hightlightTextNode(textNode) {
                var match, patternNode, wrapperNode;
                if (match = regex.exec(textNode.data)) {
                    wrapperNode = doc.createElement(o.tagName);
                    o.className && (wrapperNode.className = o.className);
                    patternNode = textNode.splitText(match.index);
                    patternNode.splitText(match[0].length);
                    wrapperNode.appendChild(patternNode.cloneNode(true));
                    textNode.parentNode.replaceChild(wrapperNode, patternNode);
                }
                return !!match;
            }
            function traverse(el, hightlightTextNode) {
                var childNode, TEXT_NODE_TYPE = 3;
                for (var i = 0; i < el.childNodes.length; i++) {
                    childNode = el.childNodes[i];
                    if (childNode.nodeType === TEXT_NODE_TYPE) {
                        i += hightlightTextNode(childNode) ? 1 : 0;
                    } else {
                        traverse(childNode, hightlightTextNode);
                    }
                }
            }
        };
        function getRegex(patterns, caseSensitive, wordsOnly) {
            var escapedPatterns = [], regexStr;
            for (var i = 0, len = patterns.length; i < len; i++) {
                escapedPatterns.push(_.escapeRegExChars(patterns[i]));
            }
            regexStr = wordsOnly ? "\\b(" + escapedPatterns.join("|") + ")\\b" : "(" + escapedPatterns.join("|") + ")";
            return caseSensitive ? new RegExp(regexStr) : new RegExp(regexStr, "i");
        }
    }(window.document);
    var Input = function() {
        "use strict";
        var specialKeyCodeMap;
        specialKeyCodeMap = {
            9: "tab",
            27: "esc",
            37: "left",
            39: "right",
            13: "enter",
            38: "up",
            40: "down"
        };
        function Input(o, www) {
            o = o || {};
            if (!o.input) {
                $.error("input is missing");
            }
            www.mixin(this);
            this.$hint = $(o.hint);
            this.$input = $(o.input);
            this.query = this.$input.val();
            this.queryWhenFocused = this.hasFocus() ? this.query : null;
            this.$overflowHelper = buildOverflowHelper(this.$input);
            this._checkLanguageDirection();
            if (this.$hint.length === 0) {
                this.setHint = this.getHint = this.clearHint = this.clearHintIfInvalid = _.noop;
            }
        }
        Input.normalizeQuery = function(str) {
            return _.toStr(str).replace(/^\s*/g, "").replace(/\s{2,}/g, " ");
        };
        _.mixin(Input.prototype, EventEmitter, {
            _onBlur: function onBlur() {
                this.resetInputValue();
                this.trigger("blurred");
            },
            _onFocus: function onFocus() {
                this.queryWhenFocused = this.query;
                this.trigger("focused");
            },
            _onKeydown: function onKeydown($e) {
                var keyName = specialKeyCodeMap[$e.which || $e.keyCode];
                this._managePreventDefault(keyName, $e);
                if (keyName && this._shouldTrigger(keyName, $e)) {
                    this.trigger(keyName + "Keyed", $e);
                }
            },
            _onInput: function onInput() {
                this._setQuery(this.getInputValue());
                this.clearHintIfInvalid();
                this._checkLanguageDirection();
            },
            _managePreventDefault: function managePreventDefault(keyName, $e) {
                var preventDefault;
                switch (keyName) {
                  case "up":
                  case "down":
                    preventDefault = !withModifier($e);
                    break;

                  default:
                    preventDefault = false;
                }
                preventDefault && $e.preventDefault();
            },
            _shouldTrigger: function shouldTrigger(keyName, $e) {
                var trigger;
                switch (keyName) {
                  case "tab":
                    trigger = !withModifier($e);
                    break;

                  default:
                    trigger = true;
                }
                return trigger;
            },
            _checkLanguageDirection: function checkLanguageDirection() {
                var dir = (this.$input.css("direction") || "ltr").toLowerCase();
                if (this.dir !== dir) {
                    this.dir = dir;
                    this.$hint.attr("dir", dir);
                    this.trigger("langDirChanged", dir);
                }
            },
            _setQuery: function setQuery(val, silent) {
                var areEquivalent, hasDifferentWhitespace;
                areEquivalent = areQueriesEquivalent(val, this.query);
                hasDifferentWhitespace = areEquivalent ? this.query.length !== val.length : false;
                this.query = val;
                if (!silent && !areEquivalent) {
                    this.trigger("queryChanged", this.query);
                } else if (!silent && hasDifferentWhitespace) {
                    this.trigger("whitespaceChanged", this.query);
                }
            },
            bind: function() {
                var that = this, onBlur, onFocus, onKeydown, onInput;
                onBlur = _.bind(this._onBlur, this);
                onFocus = _.bind(this._onFocus, this);
                onKeydown = _.bind(this._onKeydown, this);
                onInput = _.bind(this._onInput, this);
                this.$input.on("blur.tt", onBlur).on("focus.tt", onFocus).on("keydown.tt", onKeydown);
                if (!_.isMsie() || _.isMsie() > 9) {
                    this.$input.on("input.tt", onInput);
                } else {
                    this.$input.on("keydown.tt keypress.tt cut.tt paste.tt", function($e) {
                        if (specialKeyCodeMap[$e.which || $e.keyCode]) {
                            return;
                        }
                        _.defer(_.bind(that._onInput, that, $e));
                    });
                }
                return this;
            },
            focus: function focus() {
                this.$input.focus();
            },
            blur: function blur() {
                this.$input.blur();
            },
            getLangDir: function getLangDir() {
                return this.dir;
            },
            getQuery: function getQuery() {
                return this.query || "";
            },
            setQuery: function setQuery(val, silent) {
                this.setInputValue(val);
                this._setQuery(val, silent);
            },
            hasQueryChangedSinceLastFocus: function hasQueryChangedSinceLastFocus() {
                return this.query !== this.queryWhenFocused;
            },
            getInputValue: function getInputValue() {
                return this.$input.val();
            },
            setInputValue: function setInputValue(value) {
                this.$input.val(value);
                this.clearHintIfInvalid();
                this._checkLanguageDirection();
            },
            resetInputValue: function resetInputValue() {
                this.setInputValue(this.query);
            },
            getHint: function getHint() {
                return this.$hint.val();
            },
            setHint: function setHint(value) {
                this.$hint.val(value);
            },
            clearHint: function clearHint() {
                this.setHint("");
            },
            clearHintIfInvalid: function clearHintIfInvalid() {
                var val, hint, valIsPrefixOfHint, isValid;
                val = this.getInputValue();
                hint = this.getHint();
                valIsPrefixOfHint = val !== hint && hint.indexOf(val) === 0;
                isValid = val !== "" && valIsPrefixOfHint && !this.hasOverflow();
                !isValid && this.clearHint();
            },
            hasFocus: function hasFocus() {
                return this.$input.is(":focus");
            },
            hasOverflow: function hasOverflow() {
                var constraint = this.$input.width() - 2;
                this.$overflowHelper.text(this.getInputValue());
                return this.$overflowHelper.width() >= constraint;
            },
            isCursorAtEnd: function() {
                var valueLength, selectionStart, range;
                valueLength = this.$input.val().length;
                selectionStart = this.$input[0].selectionStart;
                if (_.isNumber(selectionStart)) {
                    return selectionStart === valueLength;
                } else if (document.selection) {
                    range = document.selection.createRange();
                    range.moveStart("character", -valueLength);
                    return valueLength === range.text.length;
                }
                return true;
            },
            destroy: function destroy() {
                this.$hint.off(".tt");
                this.$input.off(".tt");
                this.$overflowHelper.remove();
                this.$hint = this.$input = this.$overflowHelper = $("<div>");
            }
        });
        return Input;
        function buildOverflowHelper($input) {
            return $('<pre aria-hidden="true"></pre>').css({
                position: "absolute",
                visibility: "hidden",
                whiteSpace: "pre",
                fontFamily: $input.css("font-family"),
                fontSize: $input.css("font-size"),
                fontStyle: $input.css("font-style"),
                fontVariant: $input.css("font-variant"),
                fontWeight: $input.css("font-weight"),
                wordSpacing: $input.css("word-spacing"),
                letterSpacing: $input.css("letter-spacing"),
                textIndent: $input.css("text-indent"),
                textRendering: $input.css("text-rendering"),
                textTransform: $input.css("text-transform")
            }).insertAfter($input);
        }
        function areQueriesEquivalent(a, b) {
            return Input.normalizeQuery(a) === Input.normalizeQuery(b);
        }
        function withModifier($e) {
            return $e.altKey || $e.ctrlKey || $e.metaKey || $e.shiftKey;
        }
    }();
    var Dataset = function() {
        "use strict";
        var keys, nameGenerator;
        keys = {
            val: "tt-selectable-display",
            obj: "tt-selectable-object"
        };
        nameGenerator = _.getIdGenerator();
        function Dataset(o, www) {
            o = o || {};
            o.templates = o.templates || {};
            o.templates.notFound = o.templates.notFound || o.templates.empty;
            if (!o.source) {
                $.error("missing source");
            }
            if (!o.node) {
                $.error("missing node");
            }
            if (o.name && !isValidName(o.name)) {
                $.error("invalid dataset name: " + o.name);
            }
            www.mixin(this);
            this.highlight = !!o.highlight;
            this.name = o.name || nameGenerator();
            this.limit = o.limit || 5;
            this.displayFn = getDisplayFn(o.display || o.displayKey);
            this.templates = getTemplates(o.templates, this.displayFn);
            this.source = o.source.__ttAdapter ? o.source.__ttAdapter() : o.source;
            this.async = _.isUndefined(o.async) ? this.source.length > 2 : !!o.async;
            this._resetLastSuggestion();
            this.$el = $(o.node).addClass(this.classes.dataset).addClass(this.classes.dataset + "-" + this.name);
        }
        Dataset.extractData = function extractData(el) {
            var $el = $(el);
            if ($el.data(keys.obj)) {
                return {
                    val: $el.data(keys.val) || "",
                    obj: $el.data(keys.obj) || null
                };
            }
            return null;
        };
        _.mixin(Dataset.prototype, EventEmitter, {
            _overwrite: function overwrite(query, suggestions) {
                suggestions = suggestions || [];
                if (suggestions.length) {
                    this._renderSuggestions(query, suggestions);
                } else if (this.async && this.templates.pending) {
                    this._renderPending(query);
                } else if (!this.async && this.templates.notFound) {
                    this._renderNotFound(query);
                } else {
                    this._empty();
                }
                this.trigger("rendered", this.name, suggestions, false);
            },
            _append: function append(query, suggestions) {
                suggestions = suggestions || [];
                if (suggestions.length && this.$lastSuggestion.length) {
                    this._appendSuggestions(query, suggestions);
                } else if (suggestions.length) {
                    this._renderSuggestions(query, suggestions);
                } else if (!this.$lastSuggestion.length && this.templates.notFound) {
                    this._renderNotFound(query);
                }
                this.trigger("rendered", this.name, suggestions, true);
            },
            _renderSuggestions: function renderSuggestions(query, suggestions) {
                var $fragment;
                $fragment = this._getSuggestionsFragment(query, suggestions);
                this.$lastSuggestion = $fragment.children().last();
                this.$el.html($fragment).prepend(this._getHeader(query, suggestions)).append(this._getFooter(query, suggestions));
            },
            _appendSuggestions: function appendSuggestions(query, suggestions) {
                var $fragment, $lastSuggestion;
                $fragment = this._getSuggestionsFragment(query, suggestions);
                $lastSuggestion = $fragment.children().last();
                this.$lastSuggestion.after($fragment);
                this.$lastSuggestion = $lastSuggestion;
            },
            _renderPending: function renderPending(query) {
                var template = this.templates.pending;
                this._resetLastSuggestion();
                template && this.$el.html(template({
                    query: query,
                    dataset: this.name
                }));
            },
            _renderNotFound: function renderNotFound(query) {
                var template = this.templates.notFound;
                this._resetLastSuggestion();
                template && this.$el.html(template({
                    query: query,
                    dataset: this.name
                }));
            },
            _empty: function empty() {
                this.$el.empty();
                this._resetLastSuggestion();
            },
            _getSuggestionsFragment: function getSuggestionsFragment(query, suggestions) {
                var that = this, fragment;
                fragment = document.createDocumentFragment();
                _.each(suggestions, function getSuggestionNode(suggestion) {
                    var $el, context;
                    context = that._injectQuery(query, suggestion);
                    $el = $(that.templates.suggestion(context)).data(keys.obj, suggestion).data(keys.val, that.displayFn(suggestion)).addClass(that.classes.suggestion + " " + that.classes.selectable);
                    fragment.appendChild($el[0]);
                });
                this.highlight && highlight({
                    className: this.classes.highlight,
                    node: fragment,
                    pattern: query
                });
                return $(fragment);
            },
            _getFooter: function getFooter(query, suggestions) {
                return this.templates.footer ? this.templates.footer({
                    query: query,
                    suggestions: suggestions,
                    dataset: this.name
                }) : null;
            },
            _getHeader: function getHeader(query, suggestions) {
                return this.templates.header ? this.templates.header({
                    query: query,
                    suggestions: suggestions,
                    dataset: this.name
                }) : null;
            },
            _resetLastSuggestion: function resetLastSuggestion() {
                this.$lastSuggestion = $();
            },
            _injectQuery: function injectQuery(query, obj) {
                return _.isObject(obj) ? _.mixin({
                    _query: query
                }, obj) : obj;
            },
            update: function update(query) {
                var that = this, canceled = false, syncCalled = false, rendered = 0;
                this.cancel();
                this.cancel = function cancel() {
                    canceled = true;
                    that.cancel = $.noop;
                    that.async && that.trigger("asyncCanceled", query);
                };
                this.source(query, sync, async);
                !syncCalled && sync([]);
                function sync(suggestions) {
                    if (syncCalled) {
                        return;
                    }
                    syncCalled = true;
                    suggestions = (suggestions || []).slice(0, that.limit);
                    rendered = suggestions.length;
                    that._overwrite(query, suggestions);
                    if (rendered < that.limit && that.async) {
                        that.trigger("asyncRequested", query);
                    }
                }
                function async(suggestions) {
                    suggestions = suggestions || [];
                    if (!canceled && rendered < that.limit) {
                        that.cancel = $.noop;
                        rendered += suggestions.length;
                        that._append(query, suggestions.slice(0, that.limit - rendered));
                        that.async && that.trigger("asyncReceived", query);
                    }
                }
            },
            cancel: $.noop,
            clear: function clear() {
                this._empty();
                this.cancel();
                this.trigger("cleared");
            },
            isEmpty: function isEmpty() {
                return this.$el.is(":empty");
            },
            destroy: function destroy() {
                this.$el = $("<div>");
            }
        });
        return Dataset;
        function getDisplayFn(display) {
            display = display || _.stringify;
            return _.isFunction(display) ? display : displayFn;
            function displayFn(obj) {
                return obj[display];
            }
        }
        function getTemplates(templates, displayFn) {
            return {
                notFound: templates.notFound && _.templatify(templates.notFound),
                pending: templates.pending && _.templatify(templates.pending),
                header: templates.header && _.templatify(templates.header),
                footer: templates.footer && _.templatify(templates.footer),
                suggestion: templates.suggestion || suggestionTemplate
            };
            function suggestionTemplate(context) {
                return $("<div>").text(displayFn(context));
            }
        }
        function isValidName(str) {
            return /^[_a-zA-Z0-9-]+$/.test(str);
        }
    }();
    var Menu = function() {
        "use strict";
        function Menu(o, www) {
            var that = this;
            o = o || {};
            if (!o.node) {
                $.error("node is required");
            }
            www.mixin(this);
            this.$node = $(o.node);
            this.query = null;
            this.datasets = _.map(o.datasets, initializeDataset);
            function initializeDataset(oDataset) {
                var node = that.$node.find(oDataset.node).first();
                oDataset.node = node.length ? node : $("<div>").appendTo(that.$node);
                return new Dataset(oDataset, www);
            }
        }
        _.mixin(Menu.prototype, EventEmitter, {
            _onSelectableClick: function onSelectableClick($e) {
                this.trigger("selectableClicked", $($e.currentTarget));
            },
            _onRendered: function onRendered(type, dataset, suggestions, async) {
                this.$node.toggleClass(this.classes.empty, this._allDatasetsEmpty());
                this.trigger("datasetRendered", dataset, suggestions, async);
            },
            _onCleared: function onCleared() {
                this.$node.toggleClass(this.classes.empty, this._allDatasetsEmpty());
                this.trigger("datasetCleared");
            },
            _propagate: function propagate() {
                this.trigger.apply(this, arguments);
            },
            _allDatasetsEmpty: function allDatasetsEmpty() {
                return _.every(this.datasets, isDatasetEmpty);
                function isDatasetEmpty(dataset) {
                    return dataset.isEmpty();
                }
            },
            _getSelectables: function getSelectables() {
                return this.$node.find(this.selectors.selectable);
            },
            _removeCursor: function _removeCursor() {
                var $selectable = this.getActiveSelectable();
                $selectable && $selectable.removeClass(this.classes.cursor);
            },
            _ensureVisible: function ensureVisible($el) {
                var elTop, elBottom, nodeScrollTop, nodeHeight;
                elTop = $el.position().top;
                elBottom = elTop + $el.outerHeight(true);
                nodeScrollTop = this.$node.scrollTop();
                nodeHeight = this.$node.height() + parseInt(this.$node.css("paddingTop"), 10) + parseInt(this.$node.css("paddingBottom"), 10);
                if (elTop < 0) {
                    this.$node.scrollTop(nodeScrollTop + elTop);
                } else if (nodeHeight < elBottom) {
                    this.$node.scrollTop(nodeScrollTop + (elBottom - nodeHeight));
                }
            },
            bind: function() {
                var that = this, onSelectableClick;
                onSelectableClick = _.bind(this._onSelectableClick, this);
                this.$node.on("click.tt", this.selectors.selectable, onSelectableClick);
                _.each(this.datasets, function(dataset) {
                    dataset.onSync("asyncRequested", that._propagate, that).onSync("asyncCanceled", that._propagate, that).onSync("asyncReceived", that._propagate, that).onSync("rendered", that._onRendered, that).onSync("cleared", that._onCleared, that);
                });
                return this;
            },
            isOpen: function isOpen() {
                return this.$node.hasClass(this.classes.open);
            },
            open: function open() {
                this.$node.addClass(this.classes.open);
            },
            close: function close() {
                this.$node.removeClass(this.classes.open);
                this._removeCursor();
            },
            setLanguageDirection: function setLanguageDirection(dir) {
                this.$node.attr("dir", dir);
            },
            selectableRelativeToCursor: function selectableRelativeToCursor(delta) {
                var $selectables, $oldCursor, oldIndex, newIndex;
                $oldCursor = this.getActiveSelectable();
                $selectables = this._getSelectables();
                oldIndex = $oldCursor ? $selectables.index($oldCursor) : -1;
                newIndex = oldIndex + delta;
                newIndex = (newIndex + 1) % ($selectables.length + 1) - 1;
                newIndex = newIndex < -1 ? $selectables.length - 1 : newIndex;
                return newIndex === -1 ? null : $selectables.eq(newIndex);
            },
            setCursor: function setCursor($selectable) {
                this._removeCursor();
                if ($selectable = $selectable && $selectable.first()) {
                    $selectable.addClass(this.classes.cursor);
                    this._ensureVisible($selectable);
                }
            },
            getSelectableData: function getSelectableData($el) {
                return $el && $el.length ? Dataset.extractData($el) : null;
            },
            getActiveSelectable: function getActiveSelectable() {
                var $selectable = this._getSelectables().filter(this.selectors.cursor).first();
                return $selectable.length ? $selectable : null;
            },
            getTopSelectable: function getTopSelectable() {
                var $selectable = this._getSelectables().first();
                return $selectable.length ? $selectable : null;
            },
            update: function update(query) {
                var isValidUpdate = query !== this.query;
                if (isValidUpdate) {
                    this.query = query;
                    _.each(this.datasets, updateDataset);
                }
                return isValidUpdate;
                function updateDataset(dataset) {
                    dataset.update(query);
                }
            },
            empty: function empty() {
                _.each(this.datasets, clearDataset);
                this.query = null;
                this.$node.addClass(this.classes.empty);
                function clearDataset(dataset) {
                    dataset.clear();
                }
            },
            destroy: function destroy() {
                this.$node.off(".tt");
                this.$node = $("<div>");
                _.each(this.datasets, destroyDataset);
                function destroyDataset(dataset) {
                    dataset.destroy();
                }
            }
        });
        return Menu;
    }();
    var DefaultMenu = function() {
        "use strict";
        var s = Menu.prototype;
        function DefaultMenu() {
            Menu.apply(this, [].slice.call(arguments, 0));
        }
        _.mixin(DefaultMenu.prototype, Menu.prototype, {
            open: function open() {
                !this._allDatasetsEmpty() && this._show();
                return s.open.apply(this, [].slice.call(arguments, 0));
            },
            close: function close() {
                this._hide();
                return s.close.apply(this, [].slice.call(arguments, 0));
            },
            _onRendered: function onRendered() {
                if (this._allDatasetsEmpty()) {
                    this._hide();
                } else {
                    this.isOpen() && this._show();
                }
                return s._onRendered.apply(this, [].slice.call(arguments, 0));
            },
            _onCleared: function onCleared() {
                if (this._allDatasetsEmpty()) {
                    this._hide();
                } else {
                    this.isOpen() && this._show();
                }
                return s._onCleared.apply(this, [].slice.call(arguments, 0));
            },
            setLanguageDirection: function setLanguageDirection(dir) {
                this.$node.css(dir === "ltr" ? this.css.ltr : this.css.rtl);
                return s.setLanguageDirection.apply(this, [].slice.call(arguments, 0));
            },
            _hide: function hide() {
                this.$node.hide();
            },
            _show: function show() {
                this.$node.css("display", "block");
            }
        });
        return DefaultMenu;
    }();
    var Typeahead = function() {
        "use strict";
        function Typeahead(o, www) {
            var onFocused, onBlurred, onEnterKeyed, onTabKeyed, onEscKeyed, onUpKeyed, onDownKeyed, onLeftKeyed, onRightKeyed, onQueryChanged, onWhitespaceChanged;
            o = o || {};
            if (!o.input) {
                $.error("missing input");
            }
            if (!o.menu) {
                $.error("missing menu");
            }
            if (!o.eventBus) {
                $.error("missing event bus");
            }
            www.mixin(this);
            this.eventBus = o.eventBus;
            this.minLength = _.isNumber(o.minLength) ? o.minLength : 1;
            this.input = o.input;
            this.menu = o.menu;
            this.enabled = true;
            this.active = false;
            this.input.hasFocus() && this.activate();
            this.dir = this.input.getLangDir();
            this._hacks();
            this.menu.bind().onSync("selectableClicked", this._onSelectableClicked, this).onSync("asyncRequested", this._onAsyncRequested, this).onSync("asyncCanceled", this._onAsyncCanceled, this).onSync("asyncReceived", this._onAsyncReceived, this).onSync("datasetRendered", this._onDatasetRendered, this).onSync("datasetCleared", this._onDatasetCleared, this);
            onFocused = c(this, "activate", "open", "_onFocused");
            onBlurred = c(this, "deactivate", "_onBlurred");
            onEnterKeyed = c(this, "isActive", "isOpen", "_onEnterKeyed");
            onTabKeyed = c(this, "isActive", "isOpen", "_onTabKeyed");
            onEscKeyed = c(this, "isActive", "_onEscKeyed");
            onUpKeyed = c(this, "isActive", "open", "_onUpKeyed");
            onDownKeyed = c(this, "isActive", "open", "_onDownKeyed");
            onLeftKeyed = c(this, "isActive", "isOpen", "_onLeftKeyed");
            onRightKeyed = c(this, "isActive", "isOpen", "_onRightKeyed");
            onQueryChanged = c(this, "_openIfActive", "_onQueryChanged");
            onWhitespaceChanged = c(this, "_openIfActive", "_onWhitespaceChanged");
            this.input.bind().onSync("focused", onFocused, this).onSync("blurred", onBlurred, this).onSync("enterKeyed", onEnterKeyed, this).onSync("tabKeyed", onTabKeyed, this).onSync("escKeyed", onEscKeyed, this).onSync("upKeyed", onUpKeyed, this).onSync("downKeyed", onDownKeyed, this).onSync("leftKeyed", onLeftKeyed, this).onSync("rightKeyed", onRightKeyed, this).onSync("queryChanged", onQueryChanged, this).onSync("whitespaceChanged", onWhitespaceChanged, this).onSync("langDirChanged", this._onLangDirChanged, this);
        }
        _.mixin(Typeahead.prototype, {
            _hacks: function hacks() {
                var $input, $menu;
                $input = this.input.$input || $("<div>");
                $menu = this.menu.$node || $("<div>");
                $input.on("blur.tt", function($e) {
                    var active, isActive, hasActive;
                    active = document.activeElement;
                    isActive = $menu.is(active);
                    hasActive = $menu.has(active).length > 0;
                    if (_.isMsie() && (isActive || hasActive)) {
                        $e.preventDefault();
                        $e.stopImmediatePropagation();
                        _.defer(function() {
                            $input.focus();
                        });
                    }
                });
                $menu.on("mousedown.tt", function($e) {
                    $e.preventDefault();
                });
            },
            _onSelectableClicked: function onSelectableClicked(type, $el) {
                this.select($el);
            },
            _onDatasetCleared: function onDatasetCleared() {
                this._updateHint();
            },
            _onDatasetRendered: function onDatasetRendered(type, dataset, suggestions, async) {
                this._updateHint();
                this.eventBus.trigger("render", suggestions, async, dataset);
            },
            _onAsyncRequested: function onAsyncRequested(type, dataset, query) {
                this.eventBus.trigger("asyncrequest", query, dataset);
            },
            _onAsyncCanceled: function onAsyncCanceled(type, dataset, query) {
                this.eventBus.trigger("asynccancel", query, dataset);
            },
            _onAsyncReceived: function onAsyncReceived(type, dataset, query) {
                this.eventBus.trigger("asyncreceive", query, dataset);
            },
            _onFocused: function onFocused() {
                this._minLengthMet() && this.menu.update(this.input.getQuery());
            },
            _onBlurred: function onBlurred() {
                if (this.input.hasQueryChangedSinceLastFocus()) {
                    this.eventBus.trigger("change", this.input.getQuery());
                }
            },
            _onEnterKeyed: function onEnterKeyed(type, $e) {
                var $selectable;
                if ($selectable = this.menu.getActiveSelectable()) {
                    this.select($selectable) && $e.preventDefault();
                }
            },
            _onTabKeyed: function onTabKeyed(type, $e) {
                var $selectable;
                if ($selectable = this.menu.getActiveSelectable()) {
                    this.select($selectable) && $e.preventDefault();
                } else if ($selectable = this.menu.getTopSelectable()) {
                    this.autocomplete($selectable) && $e.preventDefault();
                }
            },
            _onEscKeyed: function onEscKeyed() {
                this.close();
            },
            _onUpKeyed: function onUpKeyed() {
                this.moveCursor(-1);
            },
            _onDownKeyed: function onDownKeyed() {
                this.moveCursor(+1);
            },
            _onLeftKeyed: function onLeftKeyed() {
                if (this.dir === "rtl" && this.input.isCursorAtEnd()) {
                    this.autocomplete(this.menu.getTopSelectable());
                }
            },
            _onRightKeyed: function onRightKeyed() {
                if (this.dir === "ltr" && this.input.isCursorAtEnd()) {
                    this.autocomplete(this.menu.getTopSelectable());
                }
            },
            _onQueryChanged: function onQueryChanged(e, query) {
                this._minLengthMet(query) ? this.menu.update(query) : this.menu.empty();
            },
            _onWhitespaceChanged: function onWhitespaceChanged() {
                this._updateHint();
            },
            _onLangDirChanged: function onLangDirChanged(e, dir) {
                if (this.dir !== dir) {
                    this.dir = dir;
                    this.menu.setLanguageDirection(dir);
                }
            },
            _openIfActive: function openIfActive() {
                this.isActive() && this.open();
            },
            _minLengthMet: function minLengthMet(query) {
                query = _.isString(query) ? query : this.input.getQuery() || "";
                return query.length >= this.minLength;
            },
            _updateHint: function updateHint() {
                var $selectable, data, val, query, escapedQuery, frontMatchRegEx, match;
                $selectable = this.menu.getTopSelectable();
                data = this.menu.getSelectableData($selectable);
                val = this.input.getInputValue();
                if (data && !_.isBlankString(val) && !this.input.hasOverflow()) {
                    query = Input.normalizeQuery(val);
                    escapedQuery = _.escapeRegExChars(query);
                    frontMatchRegEx = new RegExp("^(?:" + escapedQuery + ")(.+$)", "i");
                    match = frontMatchRegEx.exec(data.val);
                    match && this.input.setHint(val + match[1]);
                } else {
                    this.input.clearHint();
                }
            },
            isEnabled: function isEnabled() {
                return this.enabled;
            },
            enable: function enable() {
                this.enabled = true;
            },
            disable: function disable() {
                this.enabled = false;
            },
            isActive: function isActive() {
                return this.active;
            },
            activate: function activate() {
                if (this.isActive()) {
                    return true;
                } else if (!this.isEnabled() || this.eventBus.before("active")) {
                    return false;
                } else {
                    this.active = true;
                    this.eventBus.trigger("active");
                    return true;
                }
            },
            deactivate: function deactivate() {
                if (!this.isActive()) {
                    return true;
                } else if (this.eventBus.before("idle")) {
                    return false;
                } else {
                    this.active = false;
                    this.close();
                    this.eventBus.trigger("idle");
                    return true;
                }
            },
            isOpen: function isOpen() {
                return this.menu.isOpen();
            },
            open: function open() {
                if (!this.isOpen() && !this.eventBus.before("open")) {
                    this.menu.open();
                    this._updateHint();
                    this.eventBus.trigger("open");
                }
                return this.isOpen();
            },
            close: function close() {
                if (this.isOpen() && !this.eventBus.before("close")) {
                    this.menu.close();
                    this.input.clearHint();
                    this.input.resetInputValue();
                    this.eventBus.trigger("close");
                }
                return !this.isOpen();
            },
            setVal: function setVal(val) {
                this.input.setQuery(_.toStr(val));
            },
            getVal: function getVal() {
                return this.input.getQuery();
            },
            select: function select($selectable) {
                var data = this.menu.getSelectableData($selectable);
                if (data && !this.eventBus.before("select", data.obj)) {
                    this.input.setQuery(data.val, true);
                    this.eventBus.trigger("select", data.obj);
                    this.close();
                    return true;
                }
                return false;
            },
            autocomplete: function autocomplete($selectable) {
                var query, data, isValid;
                query = this.input.getQuery();
                data = this.menu.getSelectableData($selectable);
                isValid = data && query !== data.val;
                if (isValid && !this.eventBus.before("autocomplete", data.obj)) {
                    this.input.setQuery(data.val);
                    this.eventBus.trigger("autocomplete", data.obj);
                    return true;
                }
                return false;
            },
            moveCursor: function moveCursor(delta) {
                var query, $candidate, data, payload, cancelMove;
                query = this.input.getQuery();
                $candidate = this.menu.selectableRelativeToCursor(delta);
                data = this.menu.getSelectableData($candidate);
                payload = data ? data.obj : null;
                cancelMove = this._minLengthMet() && this.menu.update(query);
                if (!cancelMove && !this.eventBus.before("cursorchange", payload)) {
                    this.menu.setCursor($candidate);
                    if (data) {
                        this.input.setInputValue(data.val);
                    } else {
                        this.input.resetInputValue();
                        this._updateHint();
                    }
                    this.eventBus.trigger("cursorchange", payload);
                    return true;
                }
                return false;
            },
            destroy: function destroy() {
                this.input.destroy();
                this.menu.destroy();
            }
        });
        return Typeahead;
        function c(ctx) {
            var methods = [].slice.call(arguments, 1);
            return function() {
                var args = [].slice.call(arguments);
                _.each(methods, function(method) {
                    return ctx[method].apply(ctx, args);
                });
            };
        }
    }();
    (function() {
        "use strict";
        var old, keys, methods;
        old = $.fn.typeahead;
        keys = {
            www: "tt-www",
            attrs: "tt-attrs",
            typeahead: "tt-typeahead"
        };
        methods = {
            initialize: function initialize(o, datasets) {
                var www;
                datasets = _.isArray(datasets) ? datasets : [].slice.call(arguments, 1);
                o = o || {};
                www = WWW(o.classNames);
                return this.each(attach);
                function attach() {
                    var $input, $wrapper, $hint, $menu, defaultHint, defaultMenu, eventBus, input, menu, typeahead, MenuConstructor;
                    _.each(datasets, function(d) {
                        d.highlight = !!o.highlight;
                    });
                    $input = $(this);
                    $wrapper = $(www.html.wrapper);
                    $hint = $elOrNull(o.hint);
                    $menu = $elOrNull(o.menu);
                    defaultHint = o.hint !== false && !$hint;
                    defaultMenu = o.menu !== false && !$menu;
                    defaultHint && ($hint = buildHintFromInput($input, www));
                    defaultMenu && ($menu = $(www.html.menu).css(www.css.menu));
                    $hint && $hint.val("");
                    $input = prepInput($input, www);
                    if (defaultHint || defaultMenu) {
                        $wrapper.css(www.css.wrapper);
                        $input.css(defaultHint ? www.css.input : www.css.inputWithNoHint);
                        $input.wrap($wrapper).parent().prepend(defaultHint ? $hint : null).append(defaultMenu ? $menu : null);
                    }
                    MenuConstructor = defaultMenu ? DefaultMenu : Menu;
                    eventBus = new EventBus({
                        el: $input
                    });
                    input = new Input({
                        hint: $hint,
                        input: $input
                    }, www);
                    menu = new MenuConstructor({
                        node: $menu,
                        datasets: datasets
                    }, www);
                    typeahead = new Typeahead({
                        input: input,
                        menu: menu,
                        eventBus: eventBus,
                        minLength: o.minLength
                    }, www);
                    $input.data(keys.www, www);
                    $input.data(keys.typeahead, typeahead);
                }
            },
            isEnabled: function isEnabled() {
                var enabled;
                ttEach(this.first(), function(t) {
                    enabled = t.isEnabled();
                });
                return enabled;
            },
            enable: function enable() {
                ttEach(this, function(t) {
                    t.enable();
                });
                return this;
            },
            disable: function disable() {
                ttEach(this, function(t) {
                    t.disable();
                });
                return this;
            },
            isActive: function isActive() {
                var active;
                ttEach(this.first(), function(t) {
                    active = t.isActive();
                });
                return active;
            },
            activate: function activate() {
                ttEach(this, function(t) {
                    t.activate();
                });
                return this;
            },
            deactivate: function deactivate() {
                ttEach(this, function(t) {
                    t.deactivate();
                });
                return this;
            },
            isOpen: function isOpen() {
                var open;
                ttEach(this.first(), function(t) {
                    open = t.isOpen();
                });
                return open;
            },
            open: function open() {
                ttEach(this, function(t) {
                    t.open();
                });
                return this;
            },
            close: function close() {
                ttEach(this, function(t) {
                    t.close();
                });
                return this;
            },
            select: function select(el) {
                var success = false, $el = $(el);
                ttEach(this.first(), function(t) {
                    success = t.select($el);
                });
                return success;
            },
            autocomplete: function autocomplete(el) {
                var success = false, $el = $(el);
                ttEach(this.first(), function(t) {
                    success = t.autocomplete($el);
                });
                return success;
            },
            moveCursor: function moveCursoe(delta) {
                var success = false;
                ttEach(this.first(), function(t) {
                    success = t.moveCursor(delta);
                });
                return success;
            },
            val: function val(newVal) {
                var query;
                if (!arguments.length) {
                    ttEach(this.first(), function(t) {
                        query = t.getVal();
                    });
                    return query;
                } else {
                    ttEach(this, function(t) {
                        t.setVal(newVal);
                    });
                    return this;
                }
            },
            destroy: function destroy() {
                ttEach(this, function(typeahead, $input) {
                    revert($input);
                    typeahead.destroy();
                });
                return this;
            }
        };
        $.fn.typeahead = function(method) {
            if (methods[method]) {
                return methods[method].apply(this, [].slice.call(arguments, 1));
            } else {
                return methods.initialize.apply(this, arguments);
            }
        };
        $.fn.typeahead.noConflict = function noConflict() {
            $.fn.typeahead = old;
            return this;
        };
        function ttEach($els, fn) {
            $els.each(function() {
                var $input = $(this), typeahead;
                (typeahead = $input.data(keys.typeahead)) && fn(typeahead, $input);
            });
        }
        function buildHintFromInput($input, www) {
            return $input.clone().addClass(www.classes.hint).removeData().css(www.css.hint).css(getBackgroundStyles($input)).prop("readonly", true).removeAttr("id name placeholder required").attr({
                autocomplete: "off",
                spellcheck: "false",
                tabindex: -1
            });
        }
        function prepInput($input, www) {
            $input.data(keys.attrs, {
                dir: $input.attr("dir"),
                autocomplete: $input.attr("autocomplete"),
                spellcheck: $input.attr("spellcheck"),
                style: $input.attr("style")
            });
            $input.addClass(www.classes.input).attr({
                autocomplete: "off",
                spellcheck: false
            });
            try {
                !$input.attr("dir") && $input.attr("dir", "auto");
            } catch (e) {}
            return $input;
        }
        function getBackgroundStyles($el) {
            return {
                backgroundAttachment: $el.css("background-attachment"),
                backgroundClip: $el.css("background-clip"),
                backgroundColor: $el.css("background-color"),
                backgroundImage: $el.css("background-image"),
                backgroundOrigin: $el.css("background-origin"),
                backgroundPosition: $el.css("background-position"),
                backgroundRepeat: $el.css("background-repeat"),
                backgroundSize: $el.css("background-size")
            };
        }
        function revert($input) {
            var www, $wrapper;
            www = $input.data(keys.www);
            $wrapper = $input.parent().filter(www.selectors.wrapper);
            _.each($input.data(keys.attrs), function(val, key) {
                _.isUndefined(val) ? $input.removeAttr(key) : $input.attr(key, val);
            });
            $input.removeData(keys.typeahead).removeData(keys.www).removeData(keys.attr).removeClass(www.classes.input);
            if ($wrapper.length) {
                $input.detach().insertAfter($wrapper);
                $wrapper.remove();
            }
        }
        function $elOrNull(obj) {
            var isValid, $el;
            isValid = _.isJQuery(obj) || _.isElement(obj);
            $el = isValid ? $(obj).first() : [];
            return $el.length ? $el : null;
        }
    })();
});

//------------------------------------------------------
/*!
 * Cropper v1.0.0-rc.1
 * https://github.com/fengyuanchen/cropper
 *
 * Copyright (c) 2014-2015 Fengyuan Chen and contributors
 * Released under the MIT license
 *
 * Date: 2015-09-05T04:29:32.906Z
 */
!function(t){"function"==typeof define&&define.amd?define(["jquery"],t):t("object"==typeof exports?require("jquery"):jQuery)}(function(t){"use strict";function i(t){return"number"==typeof t&&!isNaN(t)}function e(t){return"undefined"==typeof t}function h(t,e){var h=[];return i(e)&&h.push(e),h.slice.apply(t,h)}function s(t,i){var e=h(arguments,2);return function(){return t.apply(i,e.concat(h(arguments)))}}function o(t){var i=t.match(/^(https?:)\/\/([^\:\/\?#]+):?(\d*)/i);return i&&(i[1]!==u.protocol||i[2]!==u.hostname||i[3]!==u.port)}function a(t){var i="timestamp="+(new Date).getTime();return t+(-1===t.indexOf("?")?"?":"&")+i}function n(t,i){var e;return t.naturalWidth?i(t.naturalWidth,t.naturalHeight):(e=document.createElement("img"),e.onload=function(){i(this.width,this.height)},void(e.src=t.src))}function r(t){var e=[],h=t.rotate,s=t.scaleX,o=t.scaleY;return i(h)&&e.push("rotate("+h+"deg)"),i(s)&&i(o)&&e.push("scale("+s+","+o+")"),e.length?e.join(" "):"none"}function p(t,i){var e,h,s=st(t.degree)%180,o=(s>90?180-s:s)*Math.PI/180,a=ot(o),n=at(o),r=t.width,p=t.height,d=t.aspectRatio;return i?(e=r/(n+a/d),h=e/d):(e=r*n+p*a,h=r*a+p*n),{width:e,height:h}}function d(e,h){var s,o,a,n=t("<canvas>")[0],r=n.getContext("2d"),d=0,c=0,l=h.naturalWidth,g=h.naturalHeight,u=h.rotate,m=h.scaleX,f=h.scaleY,v=i(m)&&i(f)&&(1!==m||1!==f),x=i(u)&&0!==u,w=x||v,b=l,C=g;return v&&(s=l/2,o=g/2),x&&(a=p({width:l,height:g,degree:u}),b=a.width,C=a.height,s=a.width/2,o=a.height/2),n.width=b,n.height=C,w&&(d=-l/2,c=-g/2,r.save(),r.translate(s,o)),x&&r.rotate(u*Math.PI/180),v&&r.scale(m,f),r.drawImage(e,d,c,l,g),w&&r.restore(),n}function c(i,e){this.$element=t(i),this.options=t.extend({},c.DEFAULTS,t.isPlainObject(e)&&e),this.ready=!1,this.built=!1,this.complete=!1,this.rotated=!1,this.cropped=!1,this.disabled=!1,this.replaced=!1,this.isImg=!1,this.originalUrl="",this.canvas=null,this.cropBox=null,this.init()}var l=t(window),g=t(document),u=window.location,m="cropper",f="preview."+m,v="cropper-modal",x="cropper-hide",w="cropper-hidden",b="cropper-invisible",C="cropper-move",y="cropper-crop",$="cropper-disabled",B="cropper-bg",D="mousedown touchstart pointerdown MSPointerDown",W="mousemove touchmove pointermove MSPointerMove",T="mouseup touchend touchcancel pointerup pointercancel MSPointerUp MSPointerCancel",H="wheel mousewheel DOMMouseScroll",k="dblclick",Y="load."+m,L="error."+m,M="resize."+m,X="build."+m,R="built."+m,z="cropstart."+m,E="cropmove."+m,I="cropend."+m,F="crop."+m,P="zoom."+m,O=/^(e|w|s|n|se|sw|ne|nw|all|crop|move|zoom)$/,S="e",j="w",A="s",U="n",Z="se",_="sw",q="ne",N="nw",K="all",Q="crop",G="move",J="zoom",V="none",tt=t.isFunction(t("<canvas>")[0].getContext),it=Math.sqrt,et=Math.min,ht=Math.max,st=Math.abs,ot=Math.sin,at=Math.cos,nt=parseFloat,rt={};t.extend(rt,{init:function(){var t,i=this.$element;if(i.is("img")){if(this.isImg=!0,this.originalUrl=t=i.attr("src"),!t)return;t=i.prop("src")}else i.is("canvas")&&tt&&(t=i[0].toDataURL());this.load(t)},trigger:function(i,e){var h=t.Event(i,e);return this.$element.trigger(h),h.isDefaultPrevented()},load:function(i){var e,h,s=this.options,n=this.$element,r="";i&&(this.url=i,n.one(X,s.build),this.trigger(X)||(s.checkImageOrigin&&o(i)&&(r=' crossOrigin="anonymous"',n.prop("crossOrigin")||(e=a(i))),this.$clone=h=t("<img"+r+' src="'+(e||i)+'">'),this.isImg?n[0].complete?this.start():n.one(Y,t.proxy(this.start,this)):h.one(Y,t.proxy(this.start,this)).one(L,t.proxy(this.stop,this)).addClass(x).insertAfter(n)))},start:function(){var i=this.isImg?this.$element[0]:this.$clone[0];n(i,t.proxy(function(t,i){this.image={naturalWidth:t,naturalHeight:i,aspectRatio:t/i},this.ready=!0,this.build()},this))},stop:function(){this.$clone.remove(),this.$clone=null}}),t.extend(rt,{build:function(){var i,e,h,s=this.options,o=this.$element,a=this.$clone;this.ready&&(this.built&&this.unbuild(),this.$container=o.parent(),this.$cropper=i=t(c.TEMPLATE),this.$canvas=i.find(".cropper-canvas").append(a),this.$dragBox=i.find(".cropper-drag-box"),this.$cropBox=e=i.find(".cropper-crop-box"),this.$viewBox=i.find(".cropper-view-box"),this.$face=h=e.find(".cropper-face"),o.addClass(w).after(i),this.isImg||a.removeClass(x),this.initPreview(),this.bind(),s.aspectRatio=nt(s.aspectRatio)||0/0,s.autoCrop?(this.cropped=!0,s.modal&&this.$dragBox.addClass(v)):e.addClass(w),s.guides||e.find(".cropper-dashed").addClass(w),s.center||e.find(".cropper-center").addClass(w),s.cropBoxMovable&&h.addClass(C).data("action",K),s.highlight||h.addClass(b),s.background&&i.addClass(B),s.cropBoxResizable||e.find(".cropper-line, .cropper-point").addClass(w),this.setDragMode(s.dragCrop?Q:s.movable?G:V),this.render(),this.built=!0,this.setData(s.data),o.one(R,s.built),setTimeout(t.proxy(function(){this.trigger(R),this.complete=!0},this),0))},unbuild:function(){this.built&&(this.built=!1,this.initialImage=null,this.initialCanvas=null,this.initialCropBox=null,this.container=null,this.canvas=null,this.cropBox=null,this.unbind(),this.resetPreview(),this.$preview=null,this.$viewBox=null,this.$cropBox=null,this.$dragBox=null,this.$canvas=null,this.$container=null,this.$cropper.remove(),this.$cropper=null)}}),t.extend(rt,{render:function(){this.initContainer(),this.initCanvas(),this.initCropBox(),this.renderCanvas(),this.cropped&&this.renderCropBox()},initContainer:function(){var t=this.options,i=this.$element,e=this.$container,h=this.$cropper;h.addClass(w),i.removeClass(w),h.css(this.container={width:ht(e.width(),nt(t.minContainerWidth)||200),height:ht(e.height(),nt(t.minContainerHeight)||100)}),i.addClass(w),h.removeClass(w)},initCanvas:function(){var i=this.container,e=i.width,h=i.height,s=this.image,o=s.aspectRatio,a={aspectRatio:o,width:e,height:h};h*o>e?a.height=e/o:a.width=h*o,a.oldLeft=a.left=(e-a.width)/2,a.oldTop=a.top=(h-a.height)/2,this.canvas=a,this.limitCanvas(!0,!0),this.initialImage=t.extend({},s),this.initialCanvas=t.extend({},a)},limitCanvas:function(i,e){var h,s,o=this.options,a=o.strict,n=this.container,r=n.width,p=n.height,d=this.canvas,c=d.aspectRatio,l=this.cropBox,g=this.cropped&&l,u=this.initialCanvas||d,m=u.width,f=u.height;i&&(h=nt(o.minCanvasWidth)||0,s=nt(o.minCanvasHeight)||0,h?(a&&(h=ht(g?l.width:m,h)),s=h/c):s?(a&&(s=ht(g?l.height:f,s)),h=s*c):a&&(g?(h=l.width,s=l.height,s*c>h?h=s*c:s=h/c):(h=m,s=f)),t.extend(d,{minWidth:h,minHeight:s,maxWidth:1/0,maxHeight:1/0})),e&&(a?g?(d.minLeft=et(l.left,l.left+l.width-d.width),d.minTop=et(l.top,l.top+l.height-d.height),d.maxLeft=l.left,d.maxTop=l.top):(d.minLeft=et(0,r-d.width),d.minTop=et(0,p-d.height),d.maxLeft=ht(0,r-d.width),d.maxTop=ht(0,p-d.height)):(d.minLeft=-d.width,d.minTop=-d.height,d.maxLeft=r,d.maxTop=p))},renderCanvas:function(t){var i,e,h=this.options,s=this.canvas,o=this.image;this.rotated&&(this.rotated=!1,e=p({width:o.width,height:o.height,degree:o.rotate}),i=e.width/e.height,i!==s.aspectRatio&&(s.left-=(e.width-s.width)/2,s.top-=(e.height-s.height)/2,s.width=e.width,s.height=e.height,s.aspectRatio=i,this.limitCanvas(!0,!1))),(s.width>s.maxWidth||s.width<s.minWidth)&&(s.left=s.oldLeft),(s.height>s.maxHeight||s.height<s.minHeight)&&(s.top=s.oldTop),s.width=et(ht(s.width,s.minWidth),s.maxWidth),s.height=et(ht(s.height,s.minHeight),s.maxHeight),this.limitCanvas(!1,!0),s.oldLeft=s.left=et(ht(s.left,s.minLeft),s.maxLeft),s.oldTop=s.top=et(ht(s.top,s.minTop),s.maxTop),this.$canvas.css({width:s.width,height:s.height,left:s.left,top:s.top}),this.renderImage(),this.cropped&&h.strict&&this.limitCropBox(!0,!0),t&&this.output()},renderImage:function(i){var e,h=this.canvas,s=this.image;s.rotate&&(e=p({width:h.width,height:h.height,degree:s.rotate,aspectRatio:s.aspectRatio},!0)),t.extend(s,e?{width:e.width,height:e.height,left:(h.width-e.width)/2,top:(h.height-e.height)/2}:{width:h.width,height:h.height,left:0,top:0}),this.$clone.css({width:s.width,height:s.height,marginLeft:s.left,marginTop:s.top,transform:r(s)}),i&&this.output()},initCropBox:function(){var i=this.options,e=this.canvas,h=i.aspectRatio,s=nt(i.autoCropArea)||.8,o={width:e.width,height:e.height};h&&(e.height*h>e.width?o.height=o.width/h:o.width=o.height*h),this.cropBox=o,this.limitCropBox(!0,!0),o.width=et(ht(o.width,o.minWidth),o.maxWidth),o.height=et(ht(o.height,o.minHeight),o.maxHeight),o.width=ht(o.minWidth,o.width*s),o.height=ht(o.minHeight,o.height*s),o.oldLeft=o.left=e.left+(e.width-o.width)/2,o.oldTop=o.top=e.top+(e.height-o.height)/2,this.initialCropBox=t.extend({},o)},limitCropBox:function(t,i){var e,h,s=this.options,o=s.strict,a=this.container,n=a.width,r=a.height,p=this.canvas,d=this.cropBox,c=s.aspectRatio;t&&(e=nt(s.minCropBoxWidth)||0,h=nt(s.minCropBoxHeight)||0,d.minWidth=et(n,e),d.minHeight=et(r,h),d.maxWidth=et(n,o?p.width:n),d.maxHeight=et(r,o?p.height:r),c&&(d.maxHeight*c>d.maxWidth?(d.minHeight=d.minWidth/c,d.maxHeight=d.maxWidth/c):(d.minWidth=d.minHeight*c,d.maxWidth=d.maxHeight*c)),d.minWidth=et(d.maxWidth,d.minWidth),d.minHeight=et(d.maxHeight,d.minHeight)),i&&(o?(d.minLeft=ht(0,p.left),d.minTop=ht(0,p.top),d.maxLeft=et(n,p.left+p.width)-d.width,d.maxTop=et(r,p.top+p.height)-d.height):(d.minLeft=0,d.minTop=0,d.maxLeft=n-d.width,d.maxTop=r-d.height))},renderCropBox:function(){var t=this.options,i=this.container,e=i.width,h=i.height,s=this.cropBox;(s.width>s.maxWidth||s.width<s.minWidth)&&(s.left=s.oldLeft),(s.height>s.maxHeight||s.height<s.minHeight)&&(s.top=s.oldTop),s.width=et(ht(s.width,s.minWidth),s.maxWidth),s.height=et(ht(s.height,s.minHeight),s.maxHeight),this.limitCropBox(!1,!0),s.oldLeft=s.left=et(ht(s.left,s.minLeft),s.maxLeft),s.oldTop=s.top=et(ht(s.top,s.minTop),s.maxTop),t.movable&&t.cropBoxMovable&&this.$face.data("action",s.width===e&&s.height===h?G:K),this.$cropBox.css({width:s.width,height:s.height,left:s.left,top:s.top}),this.cropped&&t.strict&&this.limitCanvas(!0,!0),this.disabled||this.output()},output:function(){this.preview(),this.complete?this.trigger(F,this.getData()):this.built||this.$element.one(R,t.proxy(function(){this.trigger(F,this.getData())},this))}}),t.extend(rt,{initPreview:function(){var i=this.url;this.$preview=t(this.options.preview),this.$viewBox.html('<img src="'+i+'">'),this.$preview.each(function(){var e=t(this);e.data(f,{width:e.width(),height:e.height(),original:e.html()}),e.html('<img src="'+i+'" style="display:block;width:100%;min-width:0!important;min-height:0!important;max-width:none!important;max-height:none!important;image-orientation:0deg!important">')})},resetPreview:function(){this.$preview.each(function(){var i=t(this);i.html(i.data(f).original).removeData(f)})},preview:function(){var i=this.image,e=this.canvas,h=this.cropBox,s=i.width,o=i.height,a=h.left-e.left-i.left,n=h.top-e.top-i.top;this.cropped&&!this.disabled&&(this.$viewBox.find("img").css({width:s,height:o,marginLeft:-a,marginTop:-n,transform:r(i)}),this.$preview.each(function(){var e=t(this),p=e.data(f),d=p.width/h.width,c=p.width,l=h.height*d;l>p.height&&(d=p.height/h.height,c=h.width*d,l=p.height),e.width(c).height(l).find("img").css({width:s*d,height:o*d,marginLeft:-a*d,marginTop:-n*d,transform:r(i)})}))}}),t.extend(rt,{bind:function(){var i=this.options,e=this.$element,h=this.$cropper;t.isFunction(i.cropstart)&&e.on(z,i.cropstart),t.isFunction(i.cropmove)&&e.on(E,i.cropmove),t.isFunction(i.cropend)&&e.on(I,i.cropend),t.isFunction(i.crop)&&e.on(F,i.crop),t.isFunction(i.zoom)&&e.on(P,i.zoom),h.on(D,t.proxy(this.cropStart,this)),i.zoomable&&i.mouseWheelZoom&&h.on(H,t.proxy(this.wheel,this)),i.doubleClickToggle&&h.on(k,t.proxy(this.dblclick,this)),g.on(W,this._cropMove=s(this.cropMove,this)).on(T,this._cropEnd=s(this.cropEnd,this)),i.responsive&&l.on(M,this._resize=s(this.resize,this))},unbind:function(){var i=this.options,e=this.$element,h=this.$cropper;t.isFunction(i.cropstart)&&e.off(z,i.cropstart),t.isFunction(i.cropmove)&&e.off(E,i.cropmove),t.isFunction(i.cropend)&&e.off(I,i.cropend),t.isFunction(i.crop)&&e.off(F,i.crop),t.isFunction(i.zoom)&&e.off(P,i.zoom),h.off(D,this.cropStart),i.zoomable&&i.mouseWheelZoom&&h.off(H,this.wheel),i.doubleClickToggle&&h.off(k,this.dblclick),g.off(W,this._cropMove).off(T,this._cropEnd),i.responsive&&l.off(M,this._resize)}}),t.extend(rt,{resize:function(){var i,e,h,s=this.$container,o=this.container;!this.disabled&&o&&(h=s.width()/o.width,(1!==h||s.height()!==o.height)&&(i=this.getCanvasData(),e=this.getCropBoxData(),this.render(),this.setCanvasData(t.each(i,function(t,e){i[t]=e*h})),this.setCropBoxData(t.each(e,function(t,i){e[t]=i*h}))))},dblclick:function(){this.disabled||this.setDragMode(this.$dragBox.hasClass(y)?G:Q)},wheel:function(t){var i=t.originalEvent,e=i,h=nt(this.options.wheelZoomRatio)||.1,s=1;this.disabled||(t.preventDefault(),e.deltaY?s=e.deltaY>0?1:-1:e.wheelDelta?s=-e.wheelDelta/120:e.detail&&(s=e.detail>0?1:-1),this.zoom(-s*h,i))},cropStart:function(i){var e,h,s=this.options,o=i.originalEvent,a=o&&o.touches,n=i;if(!this.disabled){if(a){if(e=a.length,e>1){if(!s.zoomable||!s.touchDragZoom||2!==e)return;n=a[1],this.startX2=n.pageX,this.startY2=n.pageY,h=J}n=a[0]}if(h=h||t(n.target).data("action"),O.test(h)){if(this.trigger(z,{originalEvent:o,action:h}))return;i.preventDefault(),this.action=h,this.cropping=!1,this.startX=n.pageX||o&&o.pageX,this.startY=n.pageY||o&&o.pageY,h===Q&&(this.cropping=!0,this.$dragBox.addClass(v))}}},cropMove:function(t){var i,e=this.options,h=t.originalEvent,s=h&&h.touches,o=t,a=this.action;if(!this.disabled){if(s){if(i=s.length,i>1){if(!e.zoomable||!e.touchDragZoom||2!==i)return;o=s[1],this.endX2=o.pageX,this.endY2=o.pageY}o=s[0]}if(a){if(this.trigger(E,{originalEvent:h,action:a}))return;t.preventDefault(),this.endX=o.pageX||h&&h.pageX,this.endY=o.pageY||h&&h.pageY,this.change(o.shiftKey,a===J?h:null)}}},cropEnd:function(t){var i=t.originalEvent,e=this.action;this.disabled||e&&(t.preventDefault(),this.cropping&&(this.cropping=!1,this.$dragBox.toggleClass(v,this.cropped&&this.options.modal)),this.action="",this.trigger(I,{originalEvent:i,action:e}))}}),t.extend(rt,{change:function(t,i){var e,h,s=this.options,o=s.aspectRatio,a=this.action,n=this.container,r=this.canvas,p=this.cropBox,d=p.width,c=p.height,l=p.left,g=p.top,u=l+d,m=g+c,f=0,v=0,x=n.width,b=n.height,C=!0;switch(!o&&t&&(o=d&&c?d/c:1),s.strict&&(f=p.minLeft,v=p.minTop,x=f+et(n.width,r.width),b=v+et(n.height,r.height)),h={x:this.endX-this.startX,y:this.endY-this.startY},o&&(h.X=h.y*o,h.Y=h.x/o),a){case K:l+=h.x,g+=h.y;break;case S:if(h.x>=0&&(u>=x||o&&(v>=g||m>=b))){C=!1;break}d+=h.x,o&&(c=d/o,g-=h.Y/2),0>d&&(a=j,d=0);break;case U:if(h.y<=0&&(v>=g||o&&(f>=l||u>=x))){C=!1;break}c-=h.y,g+=h.y,o&&(d=c*o,l+=h.X/2),0>c&&(a=A,c=0);break;case j:if(h.x<=0&&(f>=l||o&&(v>=g||m>=b))){C=!1;break}d-=h.x,l+=h.x,o&&(c=d/o,g+=h.Y/2),0>d&&(a=S,d=0);break;case A:if(h.y>=0&&(m>=b||o&&(f>=l||u>=x))){C=!1;break}c+=h.y,o&&(d=c*o,l-=h.X/2),0>c&&(a=U,c=0);break;case q:if(o){if(h.y<=0&&(v>=g||u>=x)){C=!1;break}c-=h.y,g+=h.y,d=c*o}else h.x>=0?x>u?d+=h.x:h.y<=0&&v>=g&&(C=!1):d+=h.x,h.y<=0?g>v&&(c-=h.y,g+=h.y):(c-=h.y,g+=h.y);0>d&&0>c?(a=_,c=0,d=0):0>d?(a=N,d=0):0>c&&(a=Z,c=0);break;case N:if(o){if(h.y<=0&&(v>=g||f>=l)){C=!1;break}c-=h.y,g+=h.y,d=c*o,l+=h.X}else h.x<=0?l>f?(d-=h.x,l+=h.x):h.y<=0&&v>=g&&(C=!1):(d-=h.x,l+=h.x),h.y<=0?g>v&&(c-=h.y,g+=h.y):(c-=h.y,g+=h.y);0>d&&0>c?(a=Z,c=0,d=0):0>d?(a=q,d=0):0>c&&(a=_,c=0);break;case _:if(o){if(h.x<=0&&(f>=l||m>=b)){C=!1;break}d-=h.x,l+=h.x,c=d/o}else h.x<=0?l>f?(d-=h.x,l+=h.x):h.y>=0&&m>=b&&(C=!1):(d-=h.x,l+=h.x),h.y>=0?b>m&&(c+=h.y):c+=h.y;0>d&&0>c?(a=q,c=0,d=0):0>d?(a=Z,d=0):0>c&&(a=N,c=0);break;case Z:if(o){if(h.x>=0&&(u>=x||m>=b)){C=!1;break}d+=h.x,c=d/o}else h.x>=0?x>u?d+=h.x:h.y>=0&&m>=b&&(C=!1):d+=h.x,h.y>=0?b>m&&(c+=h.y):c+=h.y;0>d&&0>c?(a=N,c=0,d=0):0>d?(a=_,d=0):0>c&&(a=q,c=0);break;case G:this.move(h.x,h.y),C=!1;break;case J:this.zoom(function(t,i,e,h){var s=it(t*t+i*i),o=it(e*e+h*h);return(o-s)/s}(st(this.startX-this.startX2),st(this.startY-this.startY2),st(this.endX-this.endX2),st(this.endY-this.endY2)),i),this.startX2=this.endX2,this.startY2=this.endY2,C=!1;break;case Q:h.x&&h.y&&(e=this.$cropper.offset(),l=this.startX-e.left,g=this.startY-e.top,d=p.minWidth,c=p.minHeight,h.x>0?h.y>0?a=Z:(a=q,g-=c):h.y>0?(a=_,l-=d):(a=N,l-=d,g-=c),this.cropped||(this.cropped=!0,this.$cropBox.removeClass(w)))}C&&(p.width=d,p.height=c,p.left=l,p.top=g,this.action=a,this.renderCropBox()),this.startX=this.endX,this.startY=this.endY}}),t.extend(rt,{crop:function(){this.built&&!this.disabled&&(this.cropped||(this.cropped=!0,this.limitCropBox(!0,!0),this.options.modal&&this.$dragBox.addClass(v),this.$cropBox.removeClass(w)),this.setCropBoxData(this.initialCropBox))},reset:function(){this.built&&!this.disabled&&(this.image=t.extend({},this.initialImage),this.canvas=t.extend({},this.initialCanvas),this.cropBox=t.extend({},this.initialCropBox),this.renderCanvas(),this.cropped&&this.renderCropBox())},clear:function(){this.cropped&&!this.disabled&&(t.extend(this.cropBox,{left:0,top:0,width:0,height:0}),this.cropped=!1,this.renderCropBox(),this.limitCanvas(),this.renderCanvas(),this.$dragBox.removeClass(v),this.$cropBox.addClass(w))},replace:function(t){!this.disabled&&t&&(this.isImg&&(this.replaced=!0,this.$element.attr("src",t)),this.options.data=null,this.load(t))},enable:function(){this.built&&(this.disabled=!1,this.$cropper.removeClass($))},disable:function(){this.built&&(this.disabled=!0,this.$cropper.addClass($))},destroy:function(){var t=this.$element;this.ready?(this.isImg&&this.replaced&&t.attr("src",this.originalUrl),this.unbuild(),t.removeClass(w)):this.isImg?t.off(Y,this.start):this.$clone&&this.$clone.remove(),t.removeData(m)},move:function(t,h){var s=this.canvas;e(h)&&(h=t),t=nt(t),h=nt(h),this.built&&!this.disabled&&this.options.movable&&(s.left+=i(t)?t:0,s.top+=i(h)?h:0,this.renderCanvas(!0))},zoom:function(t,i){var e,h,s=this.canvas;if(t=nt(t),t&&this.built&&!this.disabled&&this.options.zoomable){if(this.trigger(P,{originalEvent:i,ratio:t}))return;t=0>t?1/(1-t):1+t,e=s.width*t,h=s.height*t,s.left-=(e-s.width)/2,s.top-=(h-s.height)/2,s.width=e,s.height=h,this.renderCanvas(!0),this.setDragMode(G)}},rotate:function(t){var i=this.image,e=i.rotate||0;t=nt(t)||0,this.built&&!this.disabled&&this.options.rotatable&&(i.rotate=(e+t)%360,this.rotated=!0,this.renderCanvas(!0))},scale:function(t,h){var s=this.image;e(h)&&(h=t),t=nt(t),h=nt(h),this.built&&!this.disabled&&this.options.scalable&&(s.scaleX=i(t)?t:1,s.scaleY=i(h)?h:1,this.renderImage(!0))},getData:function(i){var e,h,s=this.options,o=this.image,a=this.canvas,n=this.cropBox;return this.built&&this.cropped?(h={x:n.left-a.left,y:n.top-a.top,width:n.width,height:n.height},e=o.width/o.naturalWidth,t.each(h,function(t,s){s/=e,h[t]=i?Math.round(s):s})):h={x:0,y:0,width:0,height:0},s.rotatable&&(h.rotate=o.rotate||0),s.scalable&&(h.scaleX=o.scaleX||1,h.scaleY=o.scaleY||1),h},setData:function(e){var h,s=this.image,o=this.canvas,a={};t.isFunction(e)&&(e=e.call(this.$element)),this.built&&!this.disabled&&t.isPlainObject(e)&&(i(e.rotate)&&e.rotate!==s.rotate&&this.options.rotatable&&(s.rotate=e.rotate,this.rotated=!0,this.renderCanvas(!0)),h=s.width/s.naturalWidth,i(e.x)&&(a.left=e.x*h+o.left),i(e.y)&&(a.top=e.y*h+o.top),i(e.width)&&(a.width=e.width*h),i(e.height)&&(a.height=e.height*h),this.setCropBoxData(a))},getContainerData:function(){return this.built?this.container:{}},getImageData:function(){return this.ready?this.image:{}},getCanvasData:function(){var t,i=this.canvas;return this.built&&(t={left:i.left,top:i.top,width:i.width,height:i.height}),t||{}},setCanvasData:function(e){var h=this.canvas,s=h.aspectRatio;t.isFunction(e)&&(e=e.call(this.$element)),this.built&&!this.disabled&&t.isPlainObject(e)&&(i(e.left)&&(h.left=e.left),i(e.top)&&(h.top=e.top),i(e.width)?(h.width=e.width,h.height=e.width/s):i(e.height)&&(h.height=e.height,h.width=e.height*s),this.renderCanvas(!0))},getCropBoxData:function(){var t,i=this.cropBox;return this.built&&this.cropped&&(t={left:i.left,top:i.top,width:i.width,height:i.height}),t||{}},setCropBoxData:function(e){var h,s,o=this.cropBox,a=this.options.aspectRatio;t.isFunction(e)&&(e=e.call(this.$element)),this.built&&this.cropped&&!this.disabled&&t.isPlainObject(e)&&(i(e.left)&&(o.left=e.left),i(e.top)&&(o.top=e.top),i(e.width)&&e.width!==o.width&&(h=!0,o.width=e.width),i(e.height)&&e.height!==o.height&&(s=!0,o.height=e.height),a&&(h?o.height=o.width/a:s&&(o.width=o.height*a)),this.renderCropBox())},getCroppedCanvas:function(i){var e,h,s,o,a,n,r,p,c,l,g;return this.built&&this.cropped&&tt?(t.isPlainObject(i)||(i={}),g=this.getData(),e=g.width,h=g.height,p=e/h,t.isPlainObject(i)&&(a=i.width,n=i.height,a?(n=a/p,r=a/e):n&&(a=n*p,r=n/h)),s=a||e,o=n||h,c=t("<canvas>")[0],c.width=s,c.height=o,l=c.getContext("2d"),i.fillColor&&(l.fillStyle=i.fillColor,l.fillRect(0,0,s,o)),l.drawImage.apply(l,function(){var t,i,s,o,a,n,p=d(this.$clone[0],this.image),c=p.width,l=p.height,u=[p],m=g.x,f=g.y;return-e>=m||m>c?m=t=s=a=0:0>=m?(s=-m,m=0,t=a=et(c,e+m)):c>=m&&(s=0,t=a=et(e,c-m)),0>=t||-h>=f||f>l?f=i=o=n=0:0>=f?(o=-f,f=0,i=n=et(l,h+f)):l>=f&&(o=0,i=n=et(h,l-f)),u.push(m,f,t,i),r&&(s*=r,o*=r,a*=r,n*=r),a>0&&n>0&&u.push(s,o,a,n),u}.call(this)),c):void 0},setAspectRatio:function(t){var i=this.options;this.disabled||e(t)||(i.aspectRatio=nt(t)||0/0,this.built&&(this.initCropBox(),this.cropped&&this.renderCropBox()))},setDragMode:function(t){var i,e,h=this.options;this.ready&&!this.disabled&&(i=h.dragCrop&&t===Q,e=h.movable&&t===G,t=i||e?t:V,this.$dragBox.data("action",t).toggleClass(y,i).toggleClass(C,e),h.cropBoxMovable||this.$face.data("action",t).toggleClass(y,i).toggleClass(C,e))}}),t.extend(c.prototype,rt),c.DEFAULTS={aspectRatio:0/0,data:null,preview:"",strict:!0,responsive:!0,checkImageOrigin:!0,modal:!0,guides:!0,center:!0,highlight:!0,background:!0,autoCrop:!0,autoCropArea:.8,dragCrop:!0,movable:!0,rotatable:!0,scalable:!0,zoomable:!0,mouseWheelZoom:!0,wheelZoomRatio:.1,touchDragZoom:!0,cropBoxMovable:!0,cropBoxResizable:!0,doubleClickToggle:!0,minCanvasWidth:0,minCanvasHeight:0,minCropBoxWidth:0,minCropBoxHeight:0,minContainerWidth:200,minContainerHeight:100,build:null,built:null,cropstart:null,cropmove:null,cropend:null,crop:null,zoom:null},c.setDefaults=function(i){t.extend(c.DEFAULTS,i)},c.TEMPLATE='<div class="cropper-container"><div class="cropper-canvas"></div><div class="cropper-drag-box"></div><div class="cropper-crop-box"><span class="cropper-view-box"></span><span class="cropper-dashed dashed-h"></span><span class="cropper-dashed dashed-v"></span><span class="cropper-center"></span><span class="cropper-face"></span><span class="cropper-line line-e" data-action="e"></span><span class="cropper-line line-n" data-action="n"></span><span class="cropper-line line-w" data-action="w"></span><span class="cropper-line line-s" data-action="s"></span><span class="cropper-point point-e" data-action="e"></span><span class="cropper-point point-n" data-action="n"></span><span class="cropper-point point-w" data-action="w"></span><span class="cropper-point point-s" data-action="s"></span><span class="cropper-point point-ne" data-action="ne"></span><span class="cropper-point point-nw" data-action="nw"></span><span class="cropper-point point-sw" data-action="sw"></span><span class="cropper-point point-se" data-action="se"></span></div></div>',c.other=t.fn.cropper,t.fn.cropper=function(i){var s,o=h(arguments,1);return this.each(function(){var e,h=t(this),a=h.data(m);if(!a){if(/destroy/.test(i))return;h.data(m,a=new c(this,i))}"string"==typeof i&&t.isFunction(e=a[i])&&(s=e.apply(a,o))}),e(s)?this:s},t.fn.cropper.Constructor=c,t.fn.cropper.setDefaults=c.setDefaults,t.fn.cropper.noConflict=function(){return t.fn.cropper=c.other,this}});

//------------------------------------------------------
/*
 * START :: code for cropping profile image
 */

$(document).on('change', '#profilePhoto', function(e) {
	var _this = $(this);
	var value = _this.val();

	var allowedFiles = [".jpg", ".jpeg", ".png"];
	var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:])+(" + allowedFiles.join('|') + ")$");

	if (value && value != '') {
		if (!regex.test(value.toLowerCase())) {
			toastr['error']("Please select valid image");
		} else if (this.files[0].size > 4194304) {
			toastr['error']("Image size must be less then 4MB");
		} else {

			$('#avatar-modal').modal('show');

			setTimeout(function() {
				var url = URL.createObjectURL(e.target.files[0]);
				var img = $('<img src="' + url + '">');
				$('.avatar-wrapper').empty().html('<img src="' + url + '">');
				$('.avatar-wrapper img').cropper({
					aspectRatio : 1,
					strict : true,
					crop : function(e) {
						var json = ['{"x":' + e.x, '"y":' + e.y, '"height":' + e.height, '"width":' + e.width, '"rotate":' + e.rotate + '}'].join();
						$('.avatar-data').val(json);
					}
				});

			}, 500);
		}
	} else {
		e.preventDefault();
		toastr['error']("Please select image");
	}

});

$('#avatar-modal').on('hidden.bs.modal', function() {
	$('.avatar-wrapper img').cropper('destroy');
	$('.avatar-wrapper').empty();
});


$(document).on('click', '#btnCrop', function() {
	var avatarForm = $('.avatar-form');
	var frmCont = $('#frmProfilePic');
	var url = avatarForm.attr('action');

	var data = new FormData(frmCont[0]);
	data.append('avatar_src', $('#avatar_src').val());
	data.append('avatar_data', $('#avatar_data').val());

	$.ajax(url, {
		type : 'post',
		data : data,
		dataType : 'json',
		processData : false,
		contentType : false,
		beforeSend : function() {
		},
		success : function(data) {
			if (data.state == 200) {
				$('[data-ele="profile_pic"], .img-rounded.profileimage').attr('src', data.source);
				$('#avatar-modal').modal('hide');
			} else {
				toastr['error'](data.message);
			}
		},
		complete : function() {
		}
	});
});

/*
 * END :: code for cropping profile image
 */

$.validate({
	modules : 'logic',
});


$(function(){
	$('[data-ele="select_code"], [data-ele="countryCode"], [data-ele="state"], [data-ele="city"]').selectpicker();
});

// Restrict presentation length
$('#aboutMe').restrictLength($('#pres-max-length'));

$(document).on('click', '[data-ele="submitEditProfile"]', function(e) {
	e.preventDefault();
	if ($('#editProfileForm').isValid()) {
    	submitFormHandler(ajaxUrl,'editProfileForm','Please wait..',function(){
    	    //setTimeout(function(){window.location.reload();},500);
    	});	
	}
});

$(document).on('click', '[data-ele="cancelEditProfile"]', function(e) {
    window.history.back();
});

$(document).on('change', '[data-ele="catId"]', function(e) {
	submitValueHandler(ajaxUrl, 'action=method&method=get_sub_cats&val=' + $(this).val(), 'Please wait..', function(data) {
		$('[data-ele="subcatId"]').find('option:not(:first)').remove();
		$('[data-ele="subcatId"]').append(data.html);
	});
});

$(document).on('change', '[data-ele="countryCode"]', function(e) {
	submitValueHandler(ajaxUrl, 'action=method&method=stateOptions&val=' + $(this).val(), 'Please wait..', function(data) {
		$('[data-ele="state"]').find('option:not(:first)').remove();
		$('[data-ele="state"]').append(data.html);
		$('[data-ele="state"]').selectpicker('refresh');
	});
});

$(document).on('change', '[data-ele="state"]', function(e) {
	submitValueHandler(ajaxUrl, 'action=method&method=cityOptions&val=' + $(this).val(), 'Please wait..', function(data) {
		$('[data-ele="city"]').find('option:not(:first)').remove();
		$('[data-ele="city"]').append(data.html);
		$('[data-ele="city"]').selectpicker('refresh');
	});
});

//START :: for skills tags
var substringMatcher = function(strs) {
	return function findMatches(q, cb) {
		var matches,
		    substringRegex;

		// an array that will be populated with substring matches
		matches = [];

		// regex used to determine if a string contains the substring `q`
		substrRegex = new RegExp(q, 'i');

		// iterate through the pool of strings and for any string that
		// contains the substring `q`, add it to the `matches` array
		$.each(strs, function(i, str) {
			if (substrRegex.test(str)) {
				matches.push(str);
			}
		});

		cb(matches);
	};
};

var tagApi = jQuery("[data-ele=skillsId]").tagsManager({
	prefilled : prefilledValues,
	tagsContainer : '[data-ele="skillsContainer"]',
	CapitalizeFirstLetter : true,
	tagList : allSkills,
	backspace: [],
}),
    typeahead = $("[data-ele=skillsId]").typeahead({
	hint : true,
	highlight : true,
	minLength : 1,
	
}, {
	name : 'skills',
	source : substringMatcher(allSkills)
}).on("typeahead:cursorchanged", function(e, d) {
	//tagApi.tagsManager("pushTag", d);
}).on("typeahead:selected", function(e, d) {
	tagApi.tagsManager("pushTag", d);
	typeahead.typeahead('val','');
});
//END :: for skills tags
