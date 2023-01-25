/* Begin eq/lib/underscore-1.6.0.js*/

//     Underscore.js 1.6.0
//     http://underscorejs.org
//     (c) 2009-2014 Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
//     Underscore may be freely distributed under the MIT license.

(function() {

  // Baseline setup
  // --------------

  // Establish the root object, `window` in the browser, or `exports` on the server.
  var root = this;

  // Save the previous value of the `_` variable.
  var previousUnderscore = root._;

  // Establish the object that gets returned to break out of a loop iteration.
  var breaker = {};

  // Save bytes in the minified (but not gzipped) version:
  var ArrayProto = Array.prototype, ObjProto = Object.prototype, FuncProto = Function.prototype;

  // Create quick reference variables for speed access to core prototypes.
  var
    push             = ArrayProto.push,
    slice            = ArrayProto.slice,
    concat           = ArrayProto.concat,
    toString         = ObjProto.toString,
    hasOwnProperty   = ObjProto.hasOwnProperty;

  // All **ECMAScript 5** native function implementations that we hope to use
  // are declared here.
  var
    nativeForEach      = ArrayProto.forEach,
    nativeMap          = ArrayProto.map,
    nativeReduce       = ArrayProto.reduce,
    nativeReduceRight  = ArrayProto.reduceRight,
    nativeFilter       = ArrayProto.filter,
    nativeEvery        = ArrayProto.every,
    nativeSome         = ArrayProto.some,
    nativeIndexOf      = ArrayProto.indexOf,
    nativeLastIndexOf  = ArrayProto.lastIndexOf,
    nativeIsArray      = Array.isArray,
    nativeKeys         = Object.keys,
    nativeBind         = FuncProto.bind;

  // Create a safe reference to the Underscore object for use below.
  var _ = function(obj) {
    if (obj instanceof _) return obj;
    if (!(this instanceof _)) return new _(obj);
    this._wrapped = obj;
  };

  // Export the Underscore object for **Node.js**, with
  // backwards-compatibility for the old `require()` API. If we're in
  // the browser, add `_` as a global object via a string identifier,
  // for Closure Compiler "advanced" mode.
  if (typeof exports !== 'undefined') {
    if (typeof module !== 'undefined' && module.exports) {
      exports = module.exports = _;
    }
    exports._ = _;
  } else {
    root._ = _;
  }

  // Current version.
  _.VERSION = '1.6.0';

  // Collection Functions
  // --------------------

  // The cornerstone, an `each` implementation, aka `forEach`.
  // Handles objects with the built-in `forEach`, arrays, and raw objects.
  // Delegates to **ECMAScript 5**'s native `forEach` if available.
  var each = _.each = _.forEach = function(obj, iterator, context) {
    if (obj == null) return obj;
    if (nativeForEach && obj.forEach === nativeForEach) {
      obj.forEach(iterator, context);
    } else if (obj.length === +obj.length) {
      for (var i = 0, length = obj.length; i < length; i++) {
        if (iterator.call(context, obj[i], i, obj) === breaker) return;
      }
    } else {
      var keys = _.keys(obj);
      for (var i = 0, length = keys.length; i < length; i++) {
        if (iterator.call(context, obj[keys[i]], keys[i], obj) === breaker) return;
      }
    }
    return obj;
  };

  // Return the results of applying the iterator to each element.
  // Delegates to **ECMAScript 5**'s native `map` if available.
  _.map = _.collect = function(obj, iterator, context) {
    var results = [];
    if (obj == null) return results;
    if (nativeMap && obj.map === nativeMap) return obj.map(iterator, context);
    each(obj, function(value, index, list) {
      results.push(iterator.call(context, value, index, list));
    });
    return results;
  };

  var reduceError = 'Reduce of empty array with no initial value';

  // **Reduce** builds up a single result from a list of values, aka `inject`,
  // or `foldl`. Delegates to **ECMAScript 5**'s native `reduce` if available.
  _.reduce = _.foldl = _.inject = function(obj, iterator, memo, context) {
    var initial = arguments.length > 2;
    if (obj == null) obj = [];
    if (nativeReduce && obj.reduce === nativeReduce) {
      if (context) iterator = _.bind(iterator, context);
      return initial ? obj.reduce(iterator, memo) : obj.reduce(iterator);
    }
    each(obj, function(value, index, list) {
      if (!initial) {
        memo = value;
        initial = true;
      } else {
        memo = iterator.call(context, memo, value, index, list);
      }
    });
    if (!initial) throw new TypeError(reduceError);
    return memo;
  };

  // The right-associative version of reduce, also known as `foldr`.
  // Delegates to **ECMAScript 5**'s native `reduceRight` if available.
  _.reduceRight = _.foldr = function(obj, iterator, memo, context) {
    var initial = arguments.length > 2;
    if (obj == null) obj = [];
    if (nativeReduceRight && obj.reduceRight === nativeReduceRight) {
      if (context) iterator = _.bind(iterator, context);
      return initial ? obj.reduceRight(iterator, memo) : obj.reduceRight(iterator);
    }
    var length = obj.length;
    if (length !== +length) {
      var keys = _.keys(obj);
      length = keys.length;
    }
    each(obj, function(value, index, list) {
      index = keys ? keys[--length] : --length;
      if (!initial) {
        memo = obj[index];
        initial = true;
      } else {
        memo = iterator.call(context, memo, obj[index], index, list);
      }
    });
    if (!initial) throw new TypeError(reduceError);
    return memo;
  };

  // Return the first value which passes a truth test. Aliased as `detect`.
  _.find = _.detect = function(obj, predicate, context) {
    var result;
    any(obj, function(value, index, list) {
      if (predicate.call(context, value, index, list)) {
        result = value;
        return true;
      }
    });
    return result;
  };

  // Return all the elements that pass a truth test.
  // Delegates to **ECMAScript 5**'s native `filter` if available.
  // Aliased as `select`.
  _.filter = _.select = function(obj, predicate, context) {
    var results = [];
    if (obj == null) return results;
    if (nativeFilter && obj.filter === nativeFilter) return obj.filter(predicate, context);
    each(obj, function(value, index, list) {
      if (predicate.call(context, value, index, list)) results.push(value);
    });
    return results;
  };

  // Return all the elements for which a truth test fails.
  _.reject = function(obj, predicate, context) {
    return _.filter(obj, function(value, index, list) {
      return !predicate.call(context, value, index, list);
    }, context);
  };

  // Determine whether all of the elements match a truth test.
  // Delegates to **ECMAScript 5**'s native `every` if available.
  // Aliased as `all`.
  _.every = _.all = function(obj, predicate, context) {
    predicate || (predicate = _.identity);
    var result = true;
    if (obj == null) return result;
    if (nativeEvery && obj.every === nativeEvery) return obj.every(predicate, context);
    each(obj, function(value, index, list) {
      if (!(result = result && predicate.call(context, value, index, list))) return breaker;
    });
    return !!result;
  };

  // Determine if at least one element in the object matches a truth test.
  // Delegates to **ECMAScript 5**'s native `some` if available.
  // Aliased as `any`.
  var any = _.some = _.any = function(obj, predicate, context) {
    predicate || (predicate = _.identity);
    var result = false;
    if (obj == null) return result;
    if (nativeSome && obj.some === nativeSome) return obj.some(predicate, context);
    each(obj, function(value, index, list) {
      if (result || (result = predicate.call(context, value, index, list))) return breaker;
    });
    return !!result;
  };

  // Determine if the array or object contains a given value (using `===`).
  // Aliased as `include`.
  _.contains = _.include = function(obj, target) {
    if (obj == null) return false;
    if (nativeIndexOf && obj.indexOf === nativeIndexOf) return obj.indexOf(target) != -1;
    return any(obj, function(value) {
      return value === target;
    });
  };

  // Invoke a method (with arguments) on every item in a collection.
  _.invoke = function(obj, method) {
    var args = slice.call(arguments, 2);
    var isFunc = _.isFunction(method);
    return _.map(obj, function(value) {
      return (isFunc ? method : value[method]).apply(value, args);
    });
  };

  // Convenience version of a common use case of `map`: fetching a property.
  _.pluck = function(obj, key) {
    return _.map(obj, _.property(key));
  };

  // Convenience version of a common use case of `filter`: selecting only objects
  // containing specific `key:value` pairs.
  _.where = function(obj, attrs) {
    return _.filter(obj, _.matches(attrs));
  };

  // Convenience version of a common use case of `find`: getting the first object
  // containing specific `key:value` pairs.
  _.findWhere = function(obj, attrs) {
    return _.find(obj, _.matches(attrs));
  };

  // Return the maximum element or (element-based computation).
  // Can't optimize arrays of integers longer than 65,535 elements.
  // See [WebKit Bug 80797](https://bugs.webkit.org/show_bug.cgi?id=80797)
  _.max = function(obj, iterator, context) {
    if (!iterator && _.isArray(obj) && obj[0] === +obj[0] && obj.length < 65535) {
      return Math.max.apply(Math, obj);
    }
    var result = -Infinity, lastComputed = -Infinity;
    each(obj, function(value, index, list) {
      var computed = iterator ? iterator.call(context, value, index, list) : value;
      if (computed > lastComputed) {
        result = value;
        lastComputed = computed;
      }
    });
    return result;
  };

  // Return the minimum element (or element-based computation).
  _.min = function(obj, iterator, context) {
    if (!iterator && _.isArray(obj) && obj[0] === +obj[0] && obj.length < 65535) {
      return Math.min.apply(Math, obj);
    }
    var result = Infinity, lastComputed = Infinity;
    each(obj, function(value, index, list) {
      var computed = iterator ? iterator.call(context, value, index, list) : value;
      if (computed < lastComputed) {
        result = value;
        lastComputed = computed;
      }
    });
    return result;
  };

  // Shuffle an array, using the modern version of the
  // [Fisher-Yates shuffle](http://en.wikipedia.org/wiki/Fisher–Yates_shuffle).
  _.shuffle = function(obj) {
    var rand;
    var index = 0;
    var shuffled = [];
    each(obj, function(value) {
      rand = _.random(index++);
      shuffled[index - 1] = shuffled[rand];
      shuffled[rand] = value;
    });
    return shuffled;
  };

  // Sample **n** random values from a collection.
  // If **n** is not specified, returns a single random element.
  // The internal `guard` argument allows it to work with `map`.
  _.sample = function(obj, n, guard) {
    if (n == null || guard) {
      if (obj.length !== +obj.length) obj = _.values(obj);
      return obj[_.random(obj.length - 1)];
    }
    return _.shuffle(obj).slice(0, Math.max(0, n));
  };

  // An internal function to generate lookup iterators.
  var lookupIterator = function(value) {
    if (value == null) return _.identity;
    if (_.isFunction(value)) return value;
    return _.property(value);
  };

  // Sort the object's values by a criterion produced by an iterator.
  _.sortBy = function(obj, iterator, context) {
    iterator = lookupIterator(iterator);
    return _.pluck(_.map(obj, function(value, index, list) {
      return {
        value: value,
        index: index,
        criteria: iterator.call(context, value, index, list)
      };
    }).sort(function(left, right) {
      var a = left.criteria;
      var b = right.criteria;
      if (a !== b) {
        if (a > b || a === void 0) return 1;
        if (a < b || b === void 0) return -1;
      }
      return left.index - right.index;
    }), 'value');
  };

  // An internal function used for aggregate "group by" operations.
  var group = function(behavior) {
    return function(obj, iterator, context) {
      var result = {};
      iterator = lookupIterator(iterator);
      each(obj, function(value, index) {
        var key = iterator.call(context, value, index, obj);
        behavior(result, key, value);
      });
      return result;
    };
  };

  // Groups the object's values by a criterion. Pass either a string attribute
  // to group by, or a function that returns the criterion.
  _.groupBy = group(function(result, key, value) {
    _.has(result, key) ? result[key].push(value) : result[key] = [value];
  });

  // Indexes the object's values by a criterion, similar to `groupBy`, but for
  // when you know that your index values will be unique.
  _.indexBy = group(function(result, key, value) {
    result[key] = value;
  });

  // Counts instances of an object that group by a certain criterion. Pass
  // either a string attribute to count by, or a function that returns the
  // criterion.
  _.countBy = group(function(result, key) {
    _.has(result, key) ? result[key]++ : result[key] = 1;
  });

  // Use a comparator function to figure out the smallest index at which
  // an object should be inserted so as to maintain order. Uses binary search.
  _.sortedIndex = function(array, obj, iterator, context) {
    iterator = lookupIterator(iterator);
    var value = iterator.call(context, obj);
    var low = 0, high = array.length;
    while (low < high) {
      var mid = (low + high) >>> 1;
      iterator.call(context, array[mid]) < value ? low = mid + 1 : high = mid;
    }
    return low;
  };

  // Safely create a real, live array from anything iterable.
  _.toArray = function(obj) {
    if (!obj) return [];
    if (_.isArray(obj)) return slice.call(obj);
    if (obj.length === +obj.length) return _.map(obj, _.identity);
    return _.values(obj);
  };

  // Return the number of elements in an object.
  _.size = function(obj) {
    if (obj == null) return 0;
    return (obj.length === +obj.length) ? obj.length : _.keys(obj).length;
  };

  // Array Functions
  // ---------------

  // Get the first element of an array. Passing **n** will return the first N
  // values in the array. Aliased as `head` and `take`. The **guard** check
  // allows it to work with `_.map`.
  _.first = _.head = _.take = function(array, n, guard) {
    if (array == null) return void 0;
    if ((n == null) || guard) return array[0];
    if (n < 0) return [];
    return slice.call(array, 0, n);
  };

  // Returns everything but the last entry of the array. Especially useful on
  // the arguments object. Passing **n** will return all the values in
  // the array, excluding the last N. The **guard** check allows it to work with
  // `_.map`.
  _.initial = function(array, n, guard) {
    return slice.call(array, 0, array.length - ((n == null) || guard ? 1 : n));
  };

  // Get the last element of an array. Passing **n** will return the last N
  // values in the array. The **guard** check allows it to work with `_.map`.
  _.last = function(array, n, guard) {
    if (array == null) return void 0;
    if ((n == null) || guard) return array[array.length - 1];
    return slice.call(array, Math.max(array.length - n, 0));
  };

  // Returns everything but the first entry of the array. Aliased as `tail` and `drop`.
  // Especially useful on the arguments object. Passing an **n** will return
  // the rest N values in the array. The **guard**
  // check allows it to work with `_.map`.
  _.rest = _.tail = _.drop = function(array, n, guard) {
    return slice.call(array, (n == null) || guard ? 1 : n);
  };

  // Trim out all falsy values from an array.
  _.compact = function(array) {
    return _.filter(array, _.identity);
  };

  // Internal implementation of a recursive `flatten` function.
  var flatten = function(input, shallow, output) {
    if (shallow && _.every(input, _.isArray)) {
      return concat.apply(output, input);
    }
    each(input, function(value) {
      if (_.isArray(value) || _.isArguments(value)) {
        shallow ? push.apply(output, value) : flatten(value, shallow, output);
      } else {
        output.push(value);
      }
    });
    return output;
  };

  // Flatten out an array, either recursively (by default), or just one level.
  _.flatten = function(array, shallow) {
    return flatten(array, shallow, []);
  };

  // Return a version of the array that does not contain the specified value(s).
  _.without = function(array) {
    return _.difference(array, slice.call(arguments, 1));
  };

  // Split an array into two arrays: one whose elements all satisfy the given
  // predicate, and one whose elements all do not satisfy the predicate.
  _.partition = function(array, predicate, context) {
    predicate = lookupIterator(predicate);
    var pass = [], fail = [];
    each(array, function(elem) {
      (predicate.call(context, elem) ? pass : fail).push(elem);
    });
    return [pass, fail];
  };

  // Produce a duplicate-free version of the array. If the array has already
  // been sorted, you have the option of using a faster algorithm.
  // Aliased as `unique`.
  _.uniq = _.unique = function(array, isSorted, iterator, context) {
    if (_.isFunction(isSorted)) {
      context = iterator;
      iterator = isSorted;
      isSorted = false;
    }
    var initial = iterator ? _.map(array, iterator, context) : array;
    var results = [];
    var seen = [];
    each(initial, function(value, index) {
      if (isSorted ? (!index || seen[seen.length - 1] !== value) : !_.contains(seen, value)) {
        seen.push(value);
        results.push(array[index]);
      }
    });
    return results;
  };

  // Produce an array that contains the union: each distinct element from all of
  // the passed-in arrays.
  _.union = function() {
    return _.uniq(_.flatten(arguments, true));
  };

  // Produce an array that contains every item shared between all the
  // passed-in arrays.
  _.intersection = function(array) {
    var rest = slice.call(arguments, 1);
    return _.filter(_.uniq(array), function(item) {
      return _.every(rest, function(other) {
        return _.contains(other, item);
      });
    });
  };

  // Take the difference between one array and a number of other arrays.
  // Only the elements present in just the first array will remain.
  _.difference = function(array) {
    var rest = concat.apply(ArrayProto, slice.call(arguments, 1));
    return _.filter(array, function(value){ return !_.contains(rest, value); });
  };

  // Zip together multiple lists into a single array -- elements that share
  // an index go together.
  _.zip = function() {
    var length = _.max(_.pluck(arguments, 'length').concat(0));
    var results = new Array(length);
    for (var i = 0; i < length; i++) {
      results[i] = _.pluck(arguments, '' + i);
    }
    return results;
  };

  // Converts lists into objects. Pass either a single array of `[key, value]`
  // pairs, or two parallel arrays of the same length -- one of keys, and one of
  // the corresponding values.
  _.object = function(list, values) {
    if (list == null) return {};
    var result = {};
    for (var i = 0, length = list.length; i < length; i++) {
      if (values) {
        result[list[i]] = values[i];
      } else {
        result[list[i][0]] = list[i][1];
      }
    }
    return result;
  };

  // If the browser doesn't supply us with indexOf (I'm looking at you, **MSIE**),
  // we need this function. Return the position of the first occurrence of an
  // item in an array, or -1 if the item is not included in the array.
  // Delegates to **ECMAScript 5**'s native `indexOf` if available.
  // If the array is large and already in sort order, pass `true`
  // for **isSorted** to use binary search.
  _.indexOf = function(array, item, isSorted) {
    if (array == null) return -1;
    var i = 0, length = array.length;
    if (isSorted) {
      if (typeof isSorted == 'number') {
        i = (isSorted < 0 ? Math.max(0, length + isSorted) : isSorted);
      } else {
        i = _.sortedIndex(array, item);
        return array[i] === item ? i : -1;
      }
    }
    if (nativeIndexOf && array.indexOf === nativeIndexOf) return array.indexOf(item, isSorted);
    for (; i < length; i++) if (array[i] === item) return i;
    return -1;
  };

  // Delegates to **ECMAScript 5**'s native `lastIndexOf` if available.
  _.lastIndexOf = function(array, item, from) {
    if (array == null) return -1;
    var hasIndex = from != null;
    if (nativeLastIndexOf && array.lastIndexOf === nativeLastIndexOf) {
      return hasIndex ? array.lastIndexOf(item, from) : array.lastIndexOf(item);
    }
    var i = (hasIndex ? from : array.length);
    while (i--) if (array[i] === item) return i;
    return -1;
  };

  // Generate an integer Array containing an arithmetic progression. A port of
  // the native Python `range()` function. See
  // [the Python documentation](http://docs.python.org/library/functions.html#range).
  _.range = function(start, stop, step) {
    if (arguments.length <= 1) {
      stop = start || 0;
      start = 0;
    }
    step = arguments[2] || 1;

    var length = Math.max(Math.ceil((stop - start) / step), 0);
    var idx = 0;
    var range = new Array(length);

    while(idx < length) {
      range[idx++] = start;
      start += step;
    }

    return range;
  };

  // Function (ahem) Functions
  // ------------------

  // Reusable constructor function for prototype setting.
  var ctor = function(){};

  // Create a function bound to a given object (assigning `this`, and arguments,
  // optionally). Delegates to **ECMAScript 5**'s native `Function.bind` if
  // available.
  _.bind = function(func, context) {
    var args, bound;
    if (nativeBind && func.bind === nativeBind) return nativeBind.apply(func, slice.call(arguments, 1));
    if (!_.isFunction(func)) throw new TypeError;
    args = slice.call(arguments, 2);
    return bound = function() {
      if (!(this instanceof bound)) return func.apply(context, args.concat(slice.call(arguments)));
      ctor.prototype = func.prototype;
      var self = new ctor;
      ctor.prototype = null;
      var result = func.apply(self, args.concat(slice.call(arguments)));
      if (Object(result) === result) return result;
      return self;
    };
  };

  // Partially apply a function by creating a version that has had some of its
  // arguments pre-filled, without changing its dynamic `this` context. _ acts
  // as a placeholder, allowing any combination of arguments to be pre-filled.
  _.partial = function(func) {
    var boundArgs = slice.call(arguments, 1);
    return function() {
      var position = 0;
      var args = boundArgs.slice();
      for (var i = 0, length = args.length; i < length; i++) {
        if (args[i] === _) args[i] = arguments[position++];
      }
      while (position < arguments.length) args.push(arguments[position++]);
      return func.apply(this, args);
    };
  };

  // Bind a number of an object's methods to that object. Remaining arguments
  // are the method names to be bound. Useful for ensuring that all callbacks
  // defined on an object belong to it.
  _.bindAll = function(obj) {
    var funcs = slice.call(arguments, 1);
    if (funcs.length === 0) throw new Error('bindAll must be passed function names');
    each(funcs, function(f) { obj[f] = _.bind(obj[f], obj); });
    return obj;
  };

  // Memoize an expensive function by storing its results.
  _.memoize = function(func, hasher) {
    var memo = {};
    hasher || (hasher = _.identity);
    return function() {
      var key = hasher.apply(this, arguments);
      return _.has(memo, key) ? memo[key] : (memo[key] = func.apply(this, arguments));
    };
  };

  // Delays a function for the given number of milliseconds, and then calls
  // it with the arguments supplied.
  _.delay = function(func, wait) {
    var args = slice.call(arguments, 2);
    return setTimeout(function(){ return func.apply(null, args); }, wait);
  };

  // Defers a function, scheduling it to run after the current call stack has
  // cleared.
  _.defer = function(func) {
    return _.delay.apply(_, [func, 1].concat(slice.call(arguments, 1)));
  };

  // Returns a function, that, when invoked, will only be triggered at most once
  // during a given window of time. Normally, the throttled function will run
  // as much as it can, without ever going more than once per `wait` duration;
  // but if you'd like to disable the execution on the leading edge, pass
  // `{leading: false}`. To disable execution on the trailing edge, ditto.
  _.throttle = function(func, wait, options) {
    var context, args, result;
    var timeout = null;
    var previous = 0;
    options || (options = {});
    var later = function() {
      previous = options.leading === false ? 0 : _.now();
      timeout = null;
      result = func.apply(context, args);
      context = args = null;
    };
    return function() {
      var now = _.now();
      if (!previous && options.leading === false) previous = now;
      var remaining = wait - (now - previous);
      context = this;
      args = arguments;
      if (remaining <= 0) {
        clearTimeout(timeout);
        timeout = null;
        previous = now;
        result = func.apply(context, args);
        context = args = null;
      } else if (!timeout && options.trailing !== false) {
        timeout = setTimeout(later, remaining);
      }
      return result;
    };
  };

  // Returns a function, that, as long as it continues to be invoked, will not
  // be triggered. The function will be called after it stops being called for
  // N milliseconds. If `immediate` is passed, trigger the function on the
  // leading edge, instead of the trailing.
  _.debounce = function(func, wait, immediate) {
    var timeout, args, context, timestamp, result;

    var later = function() {
      var last = _.now() - timestamp;
      if (last < wait) {
        timeout = setTimeout(later, wait - last);
      } else {
        timeout = null;
        if (!immediate) {
          result = func.apply(context, args);
          context = args = null;
        }
      }
    };

    return function() {
      context = this;
      args = arguments;
      timestamp = _.now();
      var callNow = immediate && !timeout;
      if (!timeout) {
        timeout = setTimeout(later, wait);
      }
      if (callNow) {
        result = func.apply(context, args);
        context = args = null;
      }

      return result;
    };
  };

  // Returns a function that will be executed at most one time, no matter how
  // often you call it. Useful for lazy initialization.
  _.once = function(func) {
    var ran = false, memo;
    return function() {
      if (ran) return memo;
      ran = true;
      memo = func.apply(this, arguments);
      func = null;
      return memo;
    };
  };

  // Returns the first function passed as an argument to the second,
  // allowing you to adjust arguments, run code before and after, and
  // conditionally execute the original function.
  _.wrap = function(func, wrapper) {
    return _.partial(wrapper, func);
  };

  // Returns a function that is the composition of a list of functions, each
  // consuming the return value of the function that follows.
  _.compose = function() {
    var funcs = arguments;
    return function() {
      var args = arguments;
      for (var i = funcs.length - 1; i >= 0; i--) {
        args = [funcs[i].apply(this, args)];
      }
      return args[0];
    };
  };

  // Returns a function that will only be executed after being called N times.
  _.after = function(times, func) {
    return function() {
      if (--times < 1) {
        return func.apply(this, arguments);
      }
    };
  };

  // Object Functions
  // ----------------

  // Retrieve the names of an object's properties.
  // Delegates to **ECMAScript 5**'s native `Object.keys`
  _.keys = function(obj) {
    if (!_.isObject(obj)) return [];
    if (nativeKeys) return nativeKeys(obj);
    var keys = [];
    for (var key in obj) if (_.has(obj, key)) keys.push(key);
    return keys;
  };

  // Retrieve the values of an object's properties.
  _.values = function(obj) {
    var keys = _.keys(obj);
    var length = keys.length;
    var values = new Array(length);
    for (var i = 0; i < length; i++) {
      values[i] = obj[keys[i]];
    }
    return values;
  };

  // Convert an object into a list of `[key, value]` pairs.
  _.pairs = function(obj) {
    var keys = _.keys(obj);
    var length = keys.length;
    var pairs = new Array(length);
    for (var i = 0; i < length; i++) {
      pairs[i] = [keys[i], obj[keys[i]]];
    }
    return pairs;
  };

  // Invert the keys and values of an object. The values must be serializable.
  _.invert = function(obj) {
    var result = {};
    var keys = _.keys(obj);
    for (var i = 0, length = keys.length; i < length; i++) {
      result[obj[keys[i]]] = keys[i];
    }
    return result;
  };

  // Return a sorted list of the function names available on the object.
  // Aliased as `methods`
  _.functions = _.methods = function(obj) {
    var names = [];
    for (var key in obj) {
      if (_.isFunction(obj[key])) names.push(key);
    }
    return names.sort();
  };

  // Extend a given object with all the properties in passed-in object(s).
  _.extend = function(obj) {
    each(slice.call(arguments, 1), function(source) {
      if (source) {
        for (var prop in source) {
          obj[prop] = source[prop];
        }
      }
    });
    return obj;
  };

  // Return a copy of the object only containing the whitelisted properties.
  _.pick = function(obj) {
    var copy = {};
    var keys = concat.apply(ArrayProto, slice.call(arguments, 1));
    each(keys, function(key) {
      if (key in obj) copy[key] = obj[key];
    });
    return copy;
  };

   // Return a copy of the object without the blacklisted properties.
  _.omit = function(obj) {
    var copy = {};
    var keys = concat.apply(ArrayProto, slice.call(arguments, 1));
    for (var key in obj) {
      if (!_.contains(keys, key)) copy[key] = obj[key];
    }
    return copy;
  };

  // Fill in a given object with default properties.
  _.defaults = function(obj) {
    each(slice.call(arguments, 1), function(source) {
      if (source) {
        for (var prop in source) {
          if (obj[prop] === void 0) obj[prop] = source[prop];
        }
      }
    });
    return obj;
  };

  // Create a (shallow-cloned) duplicate of an object.
  _.clone = function(obj) {
    if (!_.isObject(obj)) return obj;
    return _.isArray(obj) ? obj.slice() : _.extend({}, obj);
  };

  // Invokes interceptor with the obj, and then returns obj.
  // The primary purpose of this method is to "tap into" a method chain, in
  // order to perform operations on intermediate results within the chain.
  _.tap = function(obj, interceptor) {
    interceptor(obj);
    return obj;
  };

  // Internal recursive comparison function for `isEqual`.
  var eq = function(a, b, aStack, bStack) {
    // Identical objects are equal. `0 === -0`, but they aren't identical.
    // See the [Harmony `egal` proposal](http://wiki.ecmascript.org/doku.php?id=harmony:egal).
    if (a === b) return a !== 0 || 1 / a == 1 / b;
    // A strict comparison is necessary because `null == undefined`.
    if (a == null || b == null) return a === b;
    // Unwrap any wrapped objects.
    if (a instanceof _) a = a._wrapped;
    if (b instanceof _) b = b._wrapped;
    // Compare `[[Class]]` names.
    var className = toString.call(a);
    if (className != toString.call(b)) return false;
    switch (className) {
      // Strings, numbers, dates, and booleans are compared by value.
      case '[object String]':
        // Primitives and their corresponding object wrappers are equivalent; thus, `"5"` is
        // equivalent to `new String("5")`.
        return a == String(b);
      case '[object Number]':
        // `NaN`s are equivalent, but non-reflexive. An `egal` comparison is performed for
        // other numeric values.
        return a != +a ? b != +b : (a == 0 ? 1 / a == 1 / b : a == +b);
      case '[object Date]':
      case '[object Boolean]':
        // Coerce dates and booleans to numeric primitive values. Dates are compared by their
        // millisecond representations. Note that invalid dates with millisecond representations
        // of `NaN` are not equivalent.
        return +a == +b;
      // RegExps are compared by their source patterns and flags.
      case '[object RegExp]':
        return a.source == b.source &&
               a.global == b.global &&
               a.multiline == b.multiline &&
               a.ignoreCase == b.ignoreCase;
    }
    if (typeof a != 'object' || typeof b != 'object') return false;
    // Assume equality for cyclic structures. The algorithm for detecting cyclic
    // structures is adapted from ES 5.1 section 15.12.3, abstract operation `JO`.
    var length = aStack.length;
    while (length--) {
      // Linear search. Performance is inversely proportional to the number of
      // unique nested structures.
      if (aStack[length] == a) return bStack[length] == b;
    }
    // Objects with different constructors are not equivalent, but `Object`s
    // from different frames are.
    var aCtor = a.constructor, bCtor = b.constructor;
    if (aCtor !== bCtor && !(_.isFunction(aCtor) && (aCtor instanceof aCtor) &&
                             _.isFunction(bCtor) && (bCtor instanceof bCtor))
                        && ('constructor' in a && 'constructor' in b)) {
      return false;
    }
    // Add the first object to the stack of traversed objects.
    aStack.push(a);
    bStack.push(b);
    var size = 0, result = true;
    // Recursively compare objects and arrays.
    if (className == '[object Array]') {
      // Compare array lengths to determine if a deep comparison is necessary.
      size = a.length;
      result = size == b.length;
      if (result) {
        // Deep compare the contents, ignoring non-numeric properties.
        while (size--) {
          if (!(result = eq(a[size], b[size], aStack, bStack))) break;
        }
      }
    } else {
      // Deep compare objects.
      for (var key in a) {
        if (_.has(a, key)) {
          // Count the expected number of properties.
          size++;
          // Deep compare each member.
          if (!(result = _.has(b, key) && eq(a[key], b[key], aStack, bStack))) break;
        }
      }
      // Ensure that both objects contain the same number of properties.
      if (result) {
        for (key in b) {
          if (_.has(b, key) && !(size--)) break;
        }
        result = !size;
      }
    }
    // Remove the first object from the stack of traversed objects.
    aStack.pop();
    bStack.pop();
    return result;
  };

  // Perform a deep comparison to check if two objects are equal.
  _.isEqual = function(a, b) {
    return eq(a, b, [], []);
  };

  // Is a given array, string, or object empty?
  // An "empty" object has no enumerable own-properties.
  _.isEmpty = function(obj) {
    if (obj == null) return true;
    if (_.isArray(obj) || _.isString(obj)) return obj.length === 0;
    for (var key in obj) if (_.has(obj, key)) return false;
    return true;
  };

  // Is a given value a DOM element?
  _.isElement = function(obj) {
    return !!(obj && obj.nodeType === 1);
  };

  // Is a given value an array?
  // Delegates to ECMA5's native Array.isArray
  _.isArray = nativeIsArray || function(obj) {
    return toString.call(obj) == '[object Array]';
  };

  // Is a given variable an object?
  _.isObject = function(obj) {
    return obj === Object(obj);
  };

  // Add some isType methods: isArguments, isFunction, isString, isNumber, isDate, isRegExp.
  each(['Arguments', 'Function', 'String', 'Number', 'Date', 'RegExp'], function(name) {
    _['is' + name] = function(obj) {
      return toString.call(obj) == '[object ' + name + ']';
    };
  });

  // Define a fallback version of the method in browsers (ahem, IE), where
  // there isn't any inspectable "Arguments" type.
  if (!_.isArguments(arguments)) {
    _.isArguments = function(obj) {
      return !!(obj && _.has(obj, 'callee'));
    };
  }

  // Optimize `isFunction` if appropriate.
  if (typeof (/./) !== 'function') {
    _.isFunction = function(obj) {
      return typeof obj === 'function';
    };
  }

  // Is a given object a finite number?
  _.isFinite = function(obj) {
    return isFinite(obj) && !isNaN(parseFloat(obj));
  };

  // Is the given value `NaN`? (NaN is the only number which does not equal itself).
  _.isNaN = function(obj) {
    return _.isNumber(obj) && obj != +obj;
  };

  // Is a given value a boolean?
  _.isBoolean = function(obj) {
    return obj === true || obj === false || toString.call(obj) == '[object Boolean]';
  };

  // Is a given value equal to null?
  _.isNull = function(obj) {
    return obj === null;
  };

  // Is a given variable undefined?
  _.isUndefined = function(obj) {
    return obj === void 0;
  };

  // Shortcut function for checking if an object has a given property directly
  // on itself (in other words, not on a prototype).
  _.has = function(obj, key) {
    return hasOwnProperty.call(obj, key);
  };

  // Utility Functions
  // -----------------

  // Run Underscore.js in *noConflict* mode, returning the `_` variable to its
  // previous owner. Returns a reference to the Underscore object.
  _.noConflict = function() {
    root._ = previousUnderscore;
    return this;
  };

  // Keep the identity function around for default iterators.
  _.identity = function(value) {
    return value;
  };

  _.constant = function(value) {
    return function () {
      return value;
    };
  };

  _.property = function(key) {
    return function(obj) {
      return obj[key];
    };
  };

  // Returns a predicate for checking whether an object has a given set of `key:value` pairs.
  _.matches = function(attrs) {
    return function(obj) {
      if (obj === attrs) return true; //avoid comparing an object to itself.
      for (var key in attrs) {
        if (attrs[key] !== obj[key])
          return false;
      }
      return true;
    }
  };

  // Run a function **n** times.
  _.times = function(n, iterator, context) {
    var accum = Array(Math.max(0, n));
    for (var i = 0; i < n; i++) accum[i] = iterator.call(context, i);
    return accum;
  };

  // Return a random integer between min and max (inclusive).
  _.random = function(min, max) {
    if (max == null) {
      max = min;
      min = 0;
    }
    return min + Math.floor(Math.random() * (max - min + 1));
  };

  // A (possibly faster) way to get the current timestamp as an integer.
  _.now = Date.now || function() { return new Date().getTime(); };

  // List of HTML entities for escaping.
  var entityMap = {
    escape: {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#x27;'
    }
  };
  entityMap.unescape = _.invert(entityMap.escape);

  // Regexes containing the keys and values listed immediately above.
  var entityRegexes = {
    escape:   new RegExp('[' + _.keys(entityMap.escape).join('') + ']', 'g'),
    unescape: new RegExp('(' + _.keys(entityMap.unescape).join('|') + ')', 'g')
  };

  // Functions for escaping and unescaping strings to/from HTML interpolation.
  _.each(['escape', 'unescape'], function(method) {
    _[method] = function(string) {
      if (string == null) return '';
      return ('' + string).replace(entityRegexes[method], function(match) {
        return entityMap[method][match];
      });
    };
  });

  // If the value of the named `property` is a function then invoke it with the
  // `object` as context; otherwise, return it.
  _.result = function(object, property) {
    if (object == null) return void 0;
    var value = object[property];
    return _.isFunction(value) ? value.call(object) : value;
  };

  // Add your own custom functions to the Underscore object.
  _.mixin = function(obj) {
    each(_.functions(obj), function(name) {
      var func = _[name] = obj[name];
      _.prototype[name] = function() {
        var args = [this._wrapped];
        push.apply(args, arguments);
        return result.call(this, func.apply(_, args));
      };
    });
  };

  // Generate a unique integer id (unique within the entire client session).
  // Useful for temporary DOM ids.
  var idCounter = 0;
  _.uniqueId = function(prefix) {
    var id = ++idCounter + '';
    return prefix ? prefix + id : id;
  };

  // By default, Underscore uses ERB-style template delimiters, change the
  // following template settings to use alternative delimiters.
  _.templateSettings = {
    evaluate    : /<%([\s\S]+?)%>/g,
    interpolate : /<%=([\s\S]+?)%>/g,
    escape      : /<%-([\s\S]+?)%>/g
  };

  // When customizing `templateSettings`, if you don't want to define an
  // interpolation, evaluation or escaping regex, we need one that is
  // guaranteed not to match.
  var noMatch = /(.)^/;

  // Certain characters need to be escaped so that they can be put into a
  // string literal.
  var escapes = {
    "'":      "'",
    '\\':     '\\',
    '\r':     'r',
    '\n':     'n',
    '\t':     't',
    '\u2028': 'u2028',
    '\u2029': 'u2029'
  };

  var escaper = /\\|'|\r|\n|\t|\u2028|\u2029/g;

  // JavaScript micro-templating, similar to John Resig's implementation.
  // Underscore templating handles arbitrary delimiters, preserves whitespace,
  // and correctly escapes quotes within interpolated code.
  _.template = function(text, data, settings) {
    var render;
    settings = _.defaults({}, settings, _.templateSettings);

    // Combine delimiters into one regular expression via alternation.
    var matcher = new RegExp([
      (settings.escape || noMatch).source,
      (settings.interpolate || noMatch).source,
      (settings.evaluate || noMatch).source
    ].join('|') + '|$', 'g');

    // Compile the template source, escaping string literals appropriately.
    var index = 0;
    var source = "__p+='";
    text.replace(matcher, function(match, escape, interpolate, evaluate, offset) {
      source += text.slice(index, offset)
        .replace(escaper, function(match) { return '\\' + escapes[match]; });

      if (escape) {
        source += "'+\n((__t=(" + escape + "))==null?'':_.escape(__t))+\n'";
      }
      if (interpolate) {
        source += "'+\n((__t=(" + interpolate + "))==null?'':__t)+\n'";
      }
      if (evaluate) {
        source += "';\n" + evaluate + "\n__p+='";
      }
      index = offset + match.length;
      return match;
    });
    source += "';\n";

    // If a variable is not specified, place data values in local scope.
    if (!settings.variable) source = 'with(obj||{}){\n' + source + '}\n';

    source = "var __t,__p='',__j=Array.prototype.join," +
      "print=function(){__p+=__j.call(arguments,'');};\n" +
      source + "return __p;\n";

    try {
      render = new Function(settings.variable || 'obj', '_', source);
    } catch (e) {
      e.source = source;
      throw e;
    }

    if (data) return render(data, _);
    var template = function(data) {
      return render.call(this, data, _);
    };

    // Provide the compiled function source as a convenience for precompilation.
    template.source = 'function(' + (settings.variable || 'obj') + '){\n' + source + '}';

    return template;
  };

  // Add a "chain" function, which will delegate to the wrapper.
  _.chain = function(obj) {
    return _(obj).chain();
  };

  // OOP
  // ---------------
  // If Underscore is called as a function, it returns a wrapped object that
  // can be used OO-style. This wrapper holds altered versions of all the
  // underscore functions. Wrapped objects may be chained.

  // Helper function to continue chaining intermediate results.
  var result = function(obj) {
    return this._chain ? _(obj).chain() : obj;
  };

  // Add all of the Underscore functions to the wrapper object.
  _.mixin(_);

  // Add all mutator Array functions to the wrapper.
  each(['pop', 'push', 'reverse', 'shift', 'sort', 'splice', 'unshift'], function(name) {
    var method = ArrayProto[name];
    _.prototype[name] = function() {
      var obj = this._wrapped;
      method.apply(obj, arguments);
      if ((name == 'shift' || name == 'splice') && obj.length === 0) delete obj[0];
      return result.call(this, obj);
    };
  });

  // Add all accessor Array functions to the wrapper.
  each(['concat', 'join', 'slice'], function(name) {
    var method = ArrayProto[name];
    _.prototype[name] = function() {
      return result.call(this, method.apply(this._wrapped, arguments));
    };
  });

  _.extend(_.prototype, {

    // Start chaining a wrapped Underscore object.
    chain: function() {
      this._chain = true;
      return this;
    },

    // Extracts the result from a wrapped and chained object.
    value: function() {
      return this._wrapped;
    }

  });

  // AMD registration happens at the end for compatibility with AMD loaders
  // that may not enforce next-turn semantics on modules. Even though general
  // practice for AMD registration is to be anonymous, underscore registers
  // as a named module because, like jQuery, it is a base library that is
  // popular enough to be bundled in a third party lib, but not be part of
  // an AMD load request. Those cases could generate an error when an
  // anonymous define() is called outside of a loader request.
  if (typeof define === 'function' && define.amd) {
    define('underscore', [], function() {
      return _;
    });
  }
}).call(this);


/* End eq/lib/underscore-1.6.0.js*/

/* Begin eq/lib/mousetrap-1.4.6.js*/

/* mousetrap v1.4.6 craig.is/killing/mice */
(function(J,r,f){function s(a,b,d){a.addEventListener?a.addEventListener(b,d,!1):a.attachEvent("on"+b,d)}function A(a){if("keypress"==a.type){var b=String.fromCharCode(a.which);a.shiftKey||(b=b.toLowerCase());return b}return h[a.which]?h[a.which]:B[a.which]?B[a.which]:String.fromCharCode(a.which).toLowerCase()}function t(a){a=a||{};var b=!1,d;for(d in n)a[d]?b=!0:n[d]=0;b||(u=!1)}function C(a,b,d,c,e,v){var g,k,f=[],h=d.type;if(!l[a])return[];"keyup"==h&&w(a)&&(b=[a]);for(g=0;g<l[a].length;++g)if(k=
l[a][g],!(!c&&k.seq&&n[k.seq]!=k.level||h!=k.action||("keypress"!=h||d.metaKey||d.ctrlKey)&&b.sort().join(",")!==k.modifiers.sort().join(","))){var m=c&&k.seq==c&&k.level==v;(!c&&k.combo==e||m)&&l[a].splice(g,1);f.push(k)}return f}function K(a){var b=[];a.shiftKey&&b.push("shift");a.altKey&&b.push("alt");a.ctrlKey&&b.push("ctrl");a.metaKey&&b.push("meta");return b}function x(a,b,d,c){m.stopCallback(b,b.target||b.srcElement,d,c)||!1!==a(b,d)||(b.preventDefault?b.preventDefault():b.returnValue=!1,b.stopPropagation?
b.stopPropagation():b.cancelBubble=!0)}function y(a){"number"!==typeof a.which&&(a.which=a.keyCode);var b=A(a);b&&("keyup"==a.type&&z===b?z=!1:m.handleKey(b,K(a),a))}function w(a){return"shift"==a||"ctrl"==a||"alt"==a||"meta"==a}function L(a,b,d,c){function e(b){return function(){u=b;++n[a];clearTimeout(D);D=setTimeout(t,1E3)}}function v(b){x(d,b,a);"keyup"!==c&&(z=A(b));setTimeout(t,10)}for(var g=n[a]=0;g<b.length;++g){var f=g+1===b.length?v:e(c||E(b[g+1]).action);F(b[g],f,c,a,g)}}function E(a,b){var d,
c,e,f=[];d="+"===a?["+"]:a.split("+");for(e=0;e<d.length;++e)c=d[e],G[c]&&(c=G[c]),b&&"keypress"!=b&&H[c]&&(c=H[c],f.push("shift")),w(c)&&f.push(c);d=c;e=b;if(!e){if(!p){p={};for(var g in h)95<g&&112>g||h.hasOwnProperty(g)&&(p[h[g]]=g)}e=p[d]?"keydown":"keypress"}"keypress"==e&&f.length&&(e="keydown");return{key:c,modifiers:f,action:e}}function F(a,b,d,c,e){q[a+":"+d]=b;a=a.replace(/\s+/g," ");var f=a.split(" ");1<f.length?L(a,f,b,d):(d=E(a,d),l[d.key]=l[d.key]||[],C(d.key,d.modifiers,{type:d.action},
c,a,e),l[d.key][c?"unshift":"push"]({callback:b,modifiers:d.modifiers,action:d.action,seq:c,level:e,combo:a}))}var h={8:"backspace",9:"tab",13:"enter",16:"shift",17:"ctrl",18:"alt",20:"capslock",27:"esc",32:"space",33:"pageup",34:"pagedown",35:"end",36:"home",37:"left",38:"up",39:"right",40:"down",45:"ins",46:"del",91:"meta",93:"meta",224:"meta"},B={106:"*",107:"+",109:"-",110:".",111:"/",186:";",187:"=",188:",",189:"-",190:".",191:"/",192:"`",219:"[",220:"\\",221:"]",222:"'"},H={"~":"`","!":"1",
"@":"2","#":"3",$:"4","%":"5","^":"6","&":"7","*":"8","(":"9",")":"0",_:"-","+":"=",":":";",'"':"'","<":",",">":".","?":"/","|":"\\"},G={option:"alt",command:"meta","return":"enter",escape:"esc",mod:/Mac|iPod|iPhone|iPad/.test(navigator.platform)?"meta":"ctrl"},p,l={},q={},n={},D,z=!1,I=!1,u=!1;for(f=1;20>f;++f)h[111+f]="f"+f;for(f=0;9>=f;++f)h[f+96]=f;s(r,"keypress",y);s(r,"keydown",y);s(r,"keyup",y);var m={bind:function(a,b,d){a=a instanceof Array?a:[a];for(var c=0;c<a.length;++c)F(a[c],b,d);return this},
unbind:function(a,b){return m.bind(a,function(){},b)},trigger:function(a,b){if(q[a+":"+b])q[a+":"+b]({},a);return this},reset:function(){l={};q={};return this},stopCallback:function(a,b){return-1<(" "+b.className+" ").indexOf(" mousetrap ")?!1:"INPUT"==b.tagName||"SELECT"==b.tagName||"TEXTAREA"==b.tagName||b.isContentEditable},handleKey:function(a,b,d){var c=C(a,b,d),e;b={};var f=0,g=!1;for(e=0;e<c.length;++e)c[e].seq&&(f=Math.max(f,c[e].level));for(e=0;e<c.length;++e)c[e].seq?c[e].level==f&&(g=!0,
b[c[e].seq]=1,x(c[e].callback,d,c[e].combo,c[e].seq)):g||x(c[e].callback,d,c[e].combo);c="keypress"==d.type&&I;d.type!=u||w(a)||c||t(b);I=g&&"keydown"==d.type}};J.Mousetrap=m;"function"===typeof define&&define.amd&&define(m)})(window,document);


/* End eq/lib/mousetrap-1.4.6.js*/

/* Begin eq/lib/spin.min.js*/

// http://spin.js.org/#v2.3.2
!function(a,b){"object"==typeof module&&module.exports?module.exports=b():"function"==typeof define&&define.amd?define(b):a.Spinner=b()}(this,function(){"use strict";function a(a,b){var c,d=document.createElement(a||"div");for(c in b)d[c]=b[c];return d}function b(a){for(var b=1,c=arguments.length;c>b;b++)a.appendChild(arguments[b]);return a}function c(a,b,c,d){var e=["opacity",b,~~(100*a),c,d].join("-"),f=.01+c/d*100,g=Math.max(1-(1-a)/b*(100-f),a),h=j.substring(0,j.indexOf("Animation")).toLowerCase(),i=h&&"-"+h+"-"||"";return m[e]||(k.insertRule("@"+i+"keyframes "+e+"{0%{opacity:"+g+"}"+f+"%{opacity:"+a+"}"+(f+.01)+"%{opacity:1}"+(f+b)%100+"%{opacity:"+a+"}100%{opacity:"+g+"}}",k.cssRules.length),m[e]=1),e}function d(a,b){var c,d,e=a.style;if(b=b.charAt(0).toUpperCase()+b.slice(1),void 0!==e[b])return b;for(d=0;d<l.length;d++)if(c=l[d]+b,void 0!==e[c])return c}function e(a,b){for(var c in b)a.style[d(a,c)||c]=b[c];return a}function f(a){for(var b=1;b<arguments.length;b++){var c=arguments[b];for(var d in c)void 0===a[d]&&(a[d]=c[d])}return a}function g(a,b){return"string"==typeof a?a:a[b%a.length]}function h(a){this.opts=f(a||{},h.defaults,n)}function i(){function c(b,c){return a("<"+b+' xmlns="urn:schemas-microsoft.com:vml" class="spin-vml">',c)}k.addRule(".spin-vml","behavior:url(#default#VML)"),h.prototype.lines=function(a,d){function f(){return e(c("group",{coordsize:k+" "+k,coordorigin:-j+" "+-j}),{width:k,height:k})}function h(a,h,i){b(m,b(e(f(),{rotation:360/d.lines*a+"deg",left:~~h}),b(e(c("roundrect",{arcsize:d.corners}),{width:j,height:d.scale*d.width,left:d.scale*d.radius,top:-d.scale*d.width>>1,filter:i}),c("fill",{color:g(d.color,a),opacity:d.opacity}),c("stroke",{opacity:0}))))}var i,j=d.scale*(d.length+d.width),k=2*d.scale*j,l=-(d.width+d.length)*d.scale*2+"px",m=e(f(),{position:"absolute",top:l,left:l});if(d.shadow)for(i=1;i<=d.lines;i++)h(i,-2,"progid:DXImageTransform.Microsoft.Blur(pixelradius=2,makeshadow=1,shadowopacity=.3)");for(i=1;i<=d.lines;i++)h(i);return b(a,m)},h.prototype.opacity=function(a,b,c,d){var e=a.firstChild;d=d.shadow&&d.lines||0,e&&b+d<e.childNodes.length&&(e=e.childNodes[b+d],e=e&&e.firstChild,e=e&&e.firstChild,e&&(e.opacity=c))}}var j,k,l=["webkit","Moz","ms","O"],m={},n={lines:12,length:7,width:5,radius:10,scale:1,corners:1,color:"#000",opacity:.25,rotate:0,direction:1,speed:1,trail:100,fps:20,zIndex:2e9,className:"spinner",top:"50%",left:"50%",shadow:!1,hwaccel:!1,position:"absolute"};if(h.defaults={},f(h.prototype,{spin:function(b){this.stop();var c=this,d=c.opts,f=c.el=a(null,{className:d.className});if(e(f,{position:d.position,width:0,zIndex:d.zIndex,left:d.left,top:d.top}),b&&b.insertBefore(f,b.firstChild||null),f.setAttribute("role","progressbar"),c.lines(f,c.opts),!j){var g,h=0,i=(d.lines-1)*(1-d.direction)/2,k=d.fps,l=k/d.speed,m=(1-d.opacity)/(l*d.trail/100),n=l/d.lines;!function o(){h++;for(var a=0;a<d.lines;a++)g=Math.max(1-(h+(d.lines-a)*n)%l*m,d.opacity),c.opacity(f,a*d.direction+i,g,d);c.timeout=c.el&&setTimeout(o,~~(1e3/k))}()}return c},stop:function(){var a=this.el;return a&&(clearTimeout(this.timeout),a.parentNode&&a.parentNode.removeChild(a),this.el=void 0),this},lines:function(d,f){function h(b,c){return e(a(),{position:"absolute",width:f.scale*(f.length+f.width)+"px",height:f.scale*f.width+"px",background:b,boxShadow:c,transformOrigin:"left",transform:"rotate("+~~(360/f.lines*k+f.rotate)+"deg) translate("+f.scale*f.radius+"px,0)",borderRadius:(f.corners*f.scale*f.width>>1)+"px"})}for(var i,k=0,l=(f.lines-1)*(1-f.direction)/2;k<f.lines;k++)i=e(a(),{position:"absolute",top:1+~(f.scale*f.width/2)+"px",transform:f.hwaccel?"translate3d(0,0,0)":"",opacity:f.opacity,animation:j&&c(f.opacity,f.trail,l+k*f.direction,f.lines)+" "+1/f.speed+"s linear infinite"}),f.shadow&&b(i,e(h("#000","0 0 4px #000"),{top:"2px"})),b(d,b(i,h(g(f.color,k),"0 0 1px rgba(0,0,0,.1)")));return d},opacity:function(a,b,c){b<a.childNodes.length&&(a.childNodes[b].style.opacity=c)}}),"undefined"!=typeof document){k=function(){var c=a("style",{type:"text/css"});return b(document.getElementsByTagName("head")[0],c),c.sheet||c.styleSheet}();var o=e(a("group"),{behavior:"url(#default#VML)"});!d(o,"transform")&&o.adj?i():j=d(o,"animation")}return h});

/* End eq/lib/spin.min.js*/

/* Begin eq/js/webfont.js*/

/*
 * Copyright 2013 Small Batch, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */
;(function(window,document,undefined){
var j=void 0,k=!0,l=null,p=!1;function q(a){return function(){return this[a]}}var aa=this;function ba(a,b){var c=a.split("."),d=aa;!(c[0]in d)&&d.execScript&&d.execScript("var "+c[0]);for(var e;c.length&&(e=c.shift());)!c.length&&b!==j?d[e]=b:d=d[e]?d[e]:d[e]={}}aa.Ba=k;function ca(a,b,c){return a.call.apply(a.bind,arguments)}
function da(a,b,c){if(!a)throw Error();if(2<arguments.length){var d=Array.prototype.slice.call(arguments,2);return function(){var c=Array.prototype.slice.call(arguments);Array.prototype.unshift.apply(c,d);return a.apply(b,c)}}return function(){return a.apply(b,arguments)}}function s(a,b,c){s=Function.prototype.bind&&-1!=Function.prototype.bind.toString().indexOf("native code")?ca:da;return s.apply(l,arguments)}var ea=Date.now||function(){return+new Date};function fa(a,b){this.G=a;this.u=b||a;this.z=this.u.document;this.R=j}fa.prototype.createElement=function(a,b,c){a=this.z.createElement(a);if(b)for(var d in b)if(b.hasOwnProperty(d))if("style"==d){var e=a,f=b[d];ga(this)?e.setAttribute("style",f):e.style.cssText=f}else a.setAttribute(d,b[d]);c&&a.appendChild(this.z.createTextNode(c));return a};function t(a,b,c){a=a.z.getElementsByTagName(b)[0];a||(a=document.documentElement);a&&a.lastChild&&a.insertBefore(c,a.lastChild)}
function u(a,b){return a.createElement("link",{rel:"stylesheet",href:b})}function ha(a,b){return a.createElement("script",{src:b})}function v(a,b){for(var c=a.className.split(/\s+/),d=0,e=c.length;d<e;d++)if(c[d]==b)return;c.push(b);a.className=c.join(" ").replace(/\s+/g," ").replace(/^\s+|\s+$/,"")}function w(a,b){for(var c=a.className.split(/\s+/),d=[],e=0,f=c.length;e<f;e++)c[e]!=b&&d.push(c[e]);a.className=d.join(" ").replace(/\s+/g," ").replace(/^\s+|\s+$/,"")}
function ia(a,b){for(var c=a.className.split(/\s+/),d=0,e=c.length;d<e;d++)if(c[d]==b)return k;return p}function ga(a){if(a.R===j){var b=a.z.createElement("p");b.innerHTML='<a style="top:1px;">w</a>';a.R=/top/.test(b.getElementsByTagName("a")[0].getAttribute("style"))}return a.R}function x(a){var b=a.u.location.protocol;"about:"==b&&(b=a.G.location.protocol);return"https:"==b?"https:":"http:"};function y(a,b,c){this.w=a;this.T=b;this.Aa=c}ba("webfont.BrowserInfo",y);y.prototype.qa=q("w");y.prototype.hasWebFontSupport=y.prototype.qa;y.prototype.ra=q("T");y.prototype.hasWebKitFallbackBug=y.prototype.ra;y.prototype.sa=q("Aa");y.prototype.hasWebKitMetricsBug=y.prototype.sa;function z(a,b,c,d){this.e=a!=l?a:l;this.o=b!=l?b:l;this.ba=c!=l?c:l;this.f=d!=l?d:l}var ja=/^([0-9]+)(?:[\._-]([0-9]+))?(?:[\._-]([0-9]+))?(?:[\._+-]?(.*))?$/;z.prototype.toString=function(){return[this.e,this.o||"",this.ba||"",this.f||""].join("")};
function A(a){a=ja.exec(a);var b=l,c=l,d=l,e=l;a&&(a[1]!==l&&a[1]&&(b=parseInt(a[1],10)),a[2]!==l&&a[2]&&(c=parseInt(a[2],10)),a[3]!==l&&a[3]&&(d=parseInt(a[3],10)),a[4]!==l&&a[4]&&(e=/^[0-9]+$/.test(a[4])?parseInt(a[4],10):a[4]));return new z(b,c,d,e)};function B(a,b,c,d,e,f,g,h,n,m,r){this.J=a;this.Ha=b;this.za=c;this.ga=d;this.Fa=e;this.fa=f;this.xa=g;this.Ga=h;this.wa=n;this.ea=m;this.k=r}ba("webfont.UserAgent",B);B.prototype.getName=q("J");B.prototype.getName=B.prototype.getName;B.prototype.pa=q("za");B.prototype.getVersion=B.prototype.pa;B.prototype.la=q("ga");B.prototype.getEngine=B.prototype.la;B.prototype.ma=q("fa");B.prototype.getEngineVersion=B.prototype.ma;B.prototype.na=q("xa");B.prototype.getPlatform=B.prototype.na;B.prototype.oa=q("wa");
B.prototype.getPlatformVersion=B.prototype.oa;B.prototype.ka=q("ea");B.prototype.getDocumentMode=B.prototype.ka;B.prototype.ja=q("k");B.prototype.getBrowserInfo=B.prototype.ja;function C(a,b){this.a=a;this.H=b}var ka=new B("Unknown",new z,"Unknown","Unknown",new z,"Unknown","Unknown",new z,"Unknown",j,new y(p,p,p));
C.prototype.parse=function(){var a;if(-1!=this.a.indexOf("MSIE")){a=D(this);var b=E(this),c=A(b),d=F(this.a,/MSIE ([\d\w\.]+)/,1),e=A(d);a=new B("MSIE",e,d,"MSIE",e,d,a,c,b,G(this.H),new y("Windows"==a&&6<=e.e||"Windows Phone"==a&&8<=c.e,p,p))}else if(-1!=this.a.indexOf("Opera"))a:{a="Unknown";var b=F(this.a,/Presto\/([\d\w\.]+)/,1),c=A(b),d=E(this),e=A(d),f=G(this.H);c.e!==l?a="Presto":(-1!=this.a.indexOf("Gecko")&&(a="Gecko"),b=F(this.a,/rv:([^\)]+)/,1),c=A(b));if(-1!=this.a.indexOf("Opera Mini/")){var g=
F(this.a,/Opera Mini\/([\d\.]+)/,1),h=A(g);a=new B("OperaMini",h,g,a,c,b,D(this),e,d,f,new y(p,p,p))}else{if(-1!=this.a.indexOf("Version/")&&(g=F(this.a,/Version\/([\d\.]+)/,1),h=A(g),h.e!==l)){a=new B("Opera",h,g,a,c,b,D(this),e,d,f,new y(10<=h.e,p,p));break a}g=F(this.a,/Opera[\/ ]([\d\.]+)/,1);h=A(g);a=h.e!==l?new B("Opera",h,g,a,c,b,D(this),e,d,f,new y(10<=h.e,p,p)):new B("Opera",new z,"Unknown",a,c,b,D(this),e,d,f,new y(p,p,p))}}else if(/AppleWeb(K|k)it/.test(this.a)){a=D(this);var b=E(this),
c=A(b),d=F(this.a,/AppleWeb(?:K|k)it\/([\d\.\+]+)/,1),e=A(d),f="Unknown",g=new z,h="Unknown",n=p;-1!=this.a.indexOf("Chrome")||-1!=this.a.indexOf("CrMo")||-1!=this.a.indexOf("CriOS")?f="Chrome":/Silk\/\d/.test(this.a)?f="Silk":"BlackBerry"==a||"Android"==a?f="BuiltinBrowser":-1!=this.a.indexOf("Safari")?f="Safari":-1!=this.a.indexOf("AdobeAIR")&&(f="AdobeAIR");"BuiltinBrowser"==f?h="Unknown":"Silk"==f?h=F(this.a,/Silk\/([\d\._]+)/,1):"Chrome"==f?h=F(this.a,/(Chrome|CrMo|CriOS)\/([\d\.]+)/,2):-1!=
this.a.indexOf("Version/")?h=F(this.a,/Version\/([\d\.\w]+)/,1):"AdobeAIR"==f&&(h=F(this.a,/AdobeAIR\/([\d\.]+)/,1));g=A(h);n="AdobeAIR"==f?2<g.e||2==g.e&&5<=g.o:"BlackBerry"==a?10<=c.e:"Android"==a?2<c.e||2==c.e&&1<c.o:526<=e.e||525<=e.e&&13<=e.o;a=new B(f,g,h,"AppleWebKit",e,d,a,c,b,G(this.H),new y(n,536>e.e||536==e.e&&11>e.o,"iPhone"==a||"iPad"==a||"iPod"==a||"Macintosh"==a))}else-1!=this.a.indexOf("Gecko")?(a="Unknown",b=new z,c="Unknown",d=E(this),e=A(d),f=p,-1!=this.a.indexOf("Firefox")?(a=
"Firefox",c=F(this.a,/Firefox\/([\d\w\.]+)/,1),b=A(c),f=3<=b.e&&5<=b.o):-1!=this.a.indexOf("Mozilla")&&(a="Mozilla"),g=F(this.a,/rv:([^\)]+)/,1),h=A(g),f||(f=1<h.e||1==h.e&&9<h.o||1==h.e&&9==h.o&&2<=h.ba||g.match(/1\.9\.1b[123]/)!=l||g.match(/1\.9\.1\.[\d\.]+/)!=l),a=new B(a,b,c,"Gecko",h,g,D(this),e,d,G(this.H),new y(f,p,p))):a=ka;return a};
function D(a){var b=F(a.a,/(iPod|iPad|iPhone|Android|Windows Phone|BB\d{2}|BlackBerry)/,1);if(""!=b)return/BB\d{2}/.test(b)&&(b="BlackBerry"),b;a=F(a.a,/(Linux|Mac_PowerPC|Macintosh|Windows|CrOS)/,1);return""!=a?("Mac_PowerPC"==a&&(a="Macintosh"),a):"Unknown"}
function E(a){var b=F(a.a,/(OS X|Windows NT|Android) ([^;)]+)/,2);if(b||(b=F(a.a,/Windows Phone( OS)? ([^;)]+)/,2))||(b=F(a.a,/(iPhone )?OS ([\d_]+)/,2)))return b;if(b=F(a.a,/(?:Linux|CrOS) ([^;)]+)/,1))for(var b=b.split(/\s/),c=0;c<b.length;c+=1)if(/^[\d\._]+$/.test(b[c]))return b[c];return(a=F(a.a,/(BB\d{2}|BlackBerry).*?Version\/([^\s]*)/,2))?a:"Unknown"}function F(a,b,c){return(a=a.match(b))&&a[c]?a[c]:""}function G(a){if(a.documentMode)return a.documentMode};function la(a){this.va=a||"-"}la.prototype.f=function(a){for(var b=[],c=0;c<arguments.length;c++)b.push(arguments[c].replace(/[\W_]+/g,"").toLowerCase());return b.join(this.va)};function H(a,b){this.J=a;this.U=4;this.K="n";var c=(b||"n4").match(/^([nio])([1-9])$/i);c&&(this.K=c[1],this.U=parseInt(c[2],10))}H.prototype.getName=q("J");function I(a){return a.K+a.U}function ma(a){var b=4,c="n",d=l;a&&((d=a.match(/(normal|oblique|italic)/i))&&d[1]&&(c=d[1].substr(0,1).toLowerCase()),(d=a.match(/([1-9]00|normal|bold)/i))&&d[1]&&(/bold/i.test(d[1])?b=7:/[1-9]00/.test(d[1])&&(b=parseInt(d[1].substr(0,1),10))));return c+b};function na(a,b,c){this.c=a;this.h=b;this.M=c;this.j="wf";this.g=new la("-")}function pa(a){v(a.h,a.g.f(a.j,"loading"));J(a,"loading")}function K(a){w(a.h,a.g.f(a.j,"loading"));ia(a.h,a.g.f(a.j,"active"))||v(a.h,a.g.f(a.j,"inactive"));J(a,"inactive")}function J(a,b,c){if(a.M[b])if(c)a.M[b](c.getName(),I(c));else a.M[b]()};function L(a,b){this.c=a;this.C=b;this.s=this.c.createElement("span",{"aria-hidden":"true"},this.C)}
function M(a,b){var c=a.s,d;d=[];for(var e=b.J.split(/,\s*/),f=0;f<e.length;f++){var g=e[f].replace(/['"]/g,"");-1==g.indexOf(" ")?d.push(g):d.push("'"+g+"'")}d=d.join(",");e="normal";f=b.U+"00";"o"===b.K?e="oblique":"i"===b.K&&(e="italic");d="position:absolute;top:-99999px;left:-99999px;font-size:300px;width:auto;height:auto;line-height:normal;margin:0;padding:0;font-variant:normal;white-space:nowrap;font-family:"+d+";"+("font-style:"+e+";font-weight:"+f+";");ga(a.c)?c.setAttribute("style",d):c.style.cssText=
d}function N(a){t(a.c,"body",a.s)}L.prototype.remove=function(){var a=this.s;a.parentNode&&a.parentNode.removeChild(a)};function qa(a,b,c,d,e,f,g,h){this.V=a;this.ta=b;this.c=c;this.q=d;this.C=h||"BESbswy";this.k=e;this.F={};this.S=f||5E3;this.Z=g||l;this.B=this.A=l;a=new L(this.c,this.C);N(a);for(var n in O)O.hasOwnProperty(n)&&(M(a,new H(O[n],I(this.q))),this.F[O[n]]=a.s.offsetWidth);a.remove()}var O={Ea:"serif",Da:"sans-serif",Ca:"monospace"};
qa.prototype.start=function(){this.A=new L(this.c,this.C);N(this.A);this.B=new L(this.c,this.C);N(this.B);this.ya=ea();M(this.A,new H(this.q.getName()+",serif",I(this.q)));M(this.B,new H(this.q.getName()+",sans-serif",I(this.q)));ra(this)};function sa(a,b,c){for(var d in O)if(O.hasOwnProperty(d)&&b===a.F[O[d]]&&c===a.F[O[d]])return k;return p}
function ra(a){var b=a.A.s.offsetWidth,c=a.B.s.offsetWidth;b===a.F.serif&&c===a.F["sans-serif"]||a.k.T&&sa(a,b,c)?ea()-a.ya>=a.S?a.k.T&&sa(a,b,c)&&(a.Z===l||a.Z.hasOwnProperty(a.q.getName()))?P(a,a.V):P(a,a.ta):setTimeout(s(function(){ra(this)},a),25):P(a,a.V)}function P(a,b){a.A.remove();a.B.remove();b(a.q)};function R(a,b,c,d){this.c=b;this.t=c;this.N=0;this.ca=this.Y=p;this.S=d;this.k=a.k}function ta(a,b,c,d,e){if(0===b.length&&e)K(a.t);else{a.N+=b.length;e&&(a.Y=e);for(e=0;e<b.length;e++){var f=b[e],g=c[f.getName()],h=a.t,n=f;v(h.h,h.g.f(h.j,n.getName(),I(n).toString(),"loading"));J(h,"fontloading",n);(new qa(s(a.ha,a),s(a.ia,a),a.c,f,a.k,a.S,d,g)).start()}}}
R.prototype.ha=function(a){var b=this.t;w(b.h,b.g.f(b.j,a.getName(),I(a).toString(),"loading"));w(b.h,b.g.f(b.j,a.getName(),I(a).toString(),"inactive"));v(b.h,b.g.f(b.j,a.getName(),I(a).toString(),"active"));J(b,"fontactive",a);this.ca=k;ua(this)};R.prototype.ia=function(a){var b=this.t;w(b.h,b.g.f(b.j,a.getName(),I(a).toString(),"loading"));ia(b.h,b.g.f(b.j,a.getName(),I(a).toString(),"active"))||v(b.h,b.g.f(b.j,a.getName(),I(a).toString(),"inactive"));J(b,"fontinactive",a);ua(this)};
function ua(a){0==--a.N&&a.Y&&(a.ca?(a=a.t,w(a.h,a.g.f(a.j,"loading")),w(a.h,a.g.f(a.j,"inactive")),v(a.h,a.g.f(a.j,"active")),J(a,"active")):K(a.t))};function S(a,b,c){this.G=a;this.W=b;this.a=c;this.O=this.P=0}function T(a,b){U.W.$[a]=b}S.prototype.load=function(a){var b=a.context||this.G;this.c=new fa(this.G,b);b=new na(this.c,b.document.documentElement,a);if(this.a.k.w){var c=this.W,d=this.c,e=[],f;for(f in a)if(a.hasOwnProperty(f)){var g=c.$[f];g&&e.push(g(a[f],d))}a=a.timeout;this.O=this.P=e.length;a=new R(this.a,this.c,b,a);f=0;for(c=e.length;f<c;f++)d=e[f],d.v(this.a,s(this.ua,this,d,b,a))}else K(b)};
S.prototype.ua=function(a,b,c,d){var e=this;d?a.load(function(a,d,h){var n=0==--e.P;n&&pa(b);setTimeout(function(){ta(c,a,d||{},h||l,n)},0)}):(a=0==--this.P,this.O--,a&&(0==this.O?K(b):pa(b)),ta(c,[],{},l,a))};var va=window,wa=(new C(navigator.userAgent,document)).parse(),U=va.WebFont=new S(window,new function(){this.$={}},wa);U.load=U.load;function V(a,b){this.c=a;this.d=b}V.prototype.load=function(a){var b,c,d=this.d.urls||[],e=this.d.families||[];b=0;for(c=d.length;b<c;b++)t(this.c,"head",u(this.c,d[b]));d=[];b=0;for(c=e.length;b<c;b++){var f=e[b].split(":");if(f[1])for(var g=f[1].split(","),h=0;h<g.length;h+=1)d.push(new H(f[0],g[h]));else d.push(new H(f[0]))}a(d)};V.prototype.v=function(a,b){return b(a.k.w)};T("custom",function(a,b){return new V(b,a)});function W(a,b){this.c=a;this.d=b}var xa={regular:"n4",bold:"n7",italic:"i4",bolditalic:"i7",r:"n4",b:"n7",i:"i4",bi:"i7"};W.prototype.v=function(a,b){return b(a.k.w)};W.prototype.load=function(a){t(this.c,"head",u(this.c,x(this.c)+"//webfonts.fontslive.com/css/"+this.d.key+".css"));for(var b=this.d.families,c=[],d=0,e=b.length;d<e;d++)c.push.apply(c,ya(b[d]));a(c)};
function ya(a){var b=a.split(":");a=b[0];if(b[1]){for(var c=b[1].split(","),b=[],d=0,e=c.length;d<e;d++){var f=c[d];if(f){var g=xa[f];b.push(g?g:f)}}c=[];for(d=0;d<b.length;d+=1)c.push(new H(a,b[d]));return c}return[new H(a)]}T("ascender",function(a,b){return new W(b,a)});function X(a,b,c){this.a=a;this.c=b;this.d=c;this.m=[]}
X.prototype.v=function(a,b){var c=this,d=c.d.projectId,e=c.d.version;if(d){var f=c.c.u,g=c.c.createElement("script");g.id="__MonotypeAPIScript__"+d;var h=p;g.onload=g.onreadystatechange=function(){if(!h&&(!this.readyState||"loaded"===this.readyState||"complete"===this.readyState)){h=k;if(f["__mti_fntLst"+d]){var e=f["__mti_fntLst"+d]();if(e)for(var m=0;m<e.length;m++)c.m.push(new H(e[m].fontfamily))}b(a.k.w);g.onload=g.onreadystatechange=l}};g.src=c.D(d,e);t(this.c,"head",g)}else b(k)};
X.prototype.D=function(a,b){var c=x(this.c),d=(this.d.api||"fast.fonts.com/jsapi").replace(/^.*http(s?):(\/\/)?/,"");return c+"//"+d+"/"+a+".js"+(b?"?v="+b:"")};X.prototype.load=function(a){a(this.m)};T("monotype",function(a,b){var c=(new C(navigator.userAgent,document)).parse();return new X(c,b,a)});function Y(a,b){this.c=a;this.d=b;this.m=[]}Y.prototype.D=function(a){var b=x(this.c);return(this.d.api||b+"//use.typekit.net")+"/"+a+".js"};
Y.prototype.v=function(a,b){var c=this.d.id,d=this.d,e=this.c.u,f=this;c?(e.__webfonttypekitmodule__||(e.__webfonttypekitmodule__={}),e.__webfonttypekitmodule__[c]=function(c){c(a,d,function(a,c,d){for(var e=0;e<c.length;e+=1){var g=d[c[e]];if(g)for(var Q=0;Q<g.length;Q+=1)f.m.push(new H(c[e],g[Q]));else f.m.push(new H(c[e]))}b(a)})},c=ha(this.c,this.D(c)),t(this.c,"head",c)):b(k)};Y.prototype.load=function(a){a(this.m)};T("typekit",function(a,b){return new Y(b,a)});function za(a,b,c){this.L=a?a:b+Aa;this.p=[];this.Q=[];this.da=c||""}var Aa="//fonts.googleapis.com/css";za.prototype.f=function(){if(0==this.p.length)throw Error("No fonts to load !");if(-1!=this.L.indexOf("kit="))return this.L;for(var a=this.p.length,b=[],c=0;c<a;c++)b.push(this.p[c].replace(/ /g,"+"));a=this.L+"?family="+b.join("%7C");0<this.Q.length&&(a+="&subset="+this.Q.join(","));0<this.da.length&&(a+="&text="+encodeURIComponent(this.da));return a};function Ba(a){this.p=a;this.aa=[];this.I={}}
var Ca={latin:"BESbswy",cyrillic:"&#1081;&#1103;&#1046;",greek:"&#945;&#946;&#931;",khmer:"&#x1780;&#x1781;&#x1782;",Hanuman:"&#x1780;&#x1781;&#x1782;"},Da={thin:"1",extralight:"2","extra-light":"2",ultralight:"2","ultra-light":"2",light:"3",regular:"4",book:"4",medium:"5","semi-bold":"6",semibold:"6","demi-bold":"6",demibold:"6",bold:"7","extra-bold":"8",extrabold:"8","ultra-bold":"8",ultrabold:"8",black:"9",heavy:"9",l:"3",r:"4",b:"7"},Ea={i:"i",italic:"i",n:"n",normal:"n"},Fa=RegExp("^(thin|(?:(?:extra|ultra)-?)?light|regular|book|medium|(?:(?:semi|demi|extra|ultra)-?)?bold|black|heavy|l|r|b|[1-9]00)?(n|i|normal|italic)?$");
Ba.prototype.parse=function(){for(var a=this.p.length,b=0;b<a;b++){var c=this.p[b].split(":"),d=c[0].replace(/\+/g," "),e=["n4"];if(2<=c.length){var f;var g=c[1];f=[];if(g)for(var g=g.split(","),h=g.length,n=0;n<h;n++){var m;m=g[n];if(m.match(/^[\w]+$/)){m=Fa.exec(m.toLowerCase());var r=j;if(m==l)r="";else{r=j;r=m[1];if(r==l||""==r)r="4";else var oa=Da[r],r=oa?oa:isNaN(r)?"4":r.substr(0,1);r=[m[2]==l||""==m[2]?"n":Ea[m[2]],r].join("")}m=r}else m="";m&&f.push(m)}0<f.length&&(e=f);3==c.length&&(c=c[2],
f=[],c=!c?f:c.split(","),0<c.length&&(c=Ca[c[0]])&&(this.I[d]=c))}this.I[d]||(c=Ca[d])&&(this.I[d]=c);for(c=0;c<e.length;c+=1)this.aa.push(new H(d,e[c]))}};function Z(a,b,c){this.a=a;this.c=b;this.d=c}var Ga={Arimo:k,Cousine:k,Tinos:k};Z.prototype.v=function(a,b){b(a.k.w)};Z.prototype.load=function(a){var b=this.c;if("MSIE"==this.a.getName()&&this.d.blocking!=k){var c=s(this.X,this,a),d=function(){b.z.body?c():setTimeout(d,0)};d()}else this.X(a)};
Z.prototype.X=function(a){for(var b=this.c,c=new za(this.d.api,x(b),this.d.text),d=this.d.families,e=d.length,f=0;f<e;f++){var g=d[f].split(":");3==g.length&&c.Q.push(g.pop());var h="";2==g.length&&""!=g[1]&&(h=":");c.p.push(g.join(h))}d=new Ba(d);d.parse();t(b,"head",u(b,c.f()));a(d.aa,d.I,Ga)};T("google",function(a,b){var c=(new C(navigator.userAgent,document)).parse();return new Z(c,b,a)});function $(a,b){this.c=a;this.d=b;this.m=[]}$.prototype.D=function(a){return x(this.c)+(this.d.api||"//f.fontdeck.com/s/css/js/")+(this.c.u.location.hostname||this.c.G.location.hostname)+"/"+a+".js"};
$.prototype.v=function(a,b){var c=this.d.id,d=this.c.u,e=this;c?(d.__webfontfontdeckmodule__||(d.__webfontfontdeckmodule__={}),d.__webfontfontdeckmodule__[c]=function(a,c){for(var d=0,n=c.fonts.length;d<n;++d){var m=c.fonts[d];e.m.push(new H(m.name,ma("font-weight:"+m.weight+";font-style:"+m.style)))}b(a)},c=ha(this.c,this.D(c)),t(this.c,"head",c)):b(k)};$.prototype.load=function(a){a(this.m)};T("fontdeck",function(a,b){return new $(b,a)});window.WebFontConfig&&U.load(window.WebFontConfig);
})(this,document);


/* End eq/js/webfont.js*/

/* Begin eq/js/property.js*/

function Property(ctx, propName, initialValue, methods) {
  // methods should be get, set, compute, updateDom.
  this.uniqueId = ++Property.uniqueId;
  this.isAlreadyComputed = false;
  this.ctx = ctx;
  this.propName = propName;
  this.value = initialValue;
  var self = this;
  Object.defineProperty(ctx, propName,{
      get: function() {
          if (!self.isAlreadyComputed && Property.isComputing) {
            self.compute();
          }
          return methods.get.call(ctx);
      },
      set: function(value) {
          methods.set.call(ctx, value);
      }
    });
    this.compute = function() {
      if (!self.isAlreadyComputed) {
        var oldValue = self.value;
        // ** NOTE: Do not reference the property being computed
        // in the compute method using "this" (e.g. this.prop1).
        // This will cause an infinite loop, and stack overflow.
        // Instead, reference the corresponding private variable
        // in the constructor.
        self.value = methods.compute.call(ctx);
        if (typeof Property.postComputeHooks[self.propName] !== "undefined") {
          self.value = Property.postComputeHooks[self.propName].call(ctx, self.value);
        }
        if (typeof Property.postComputeHooks["all"] !== "undefined") {
          self.value = Property.postComputeHooks["all"].call(ctx, self.value, self.propName);
        }
        ctx[propName] = self.value;
        self.isAlreadyComputed = true;
        Property.alreadyComputed.push(self);
        self.updateDom(oldValue);
      }
    };
    this.updateDom = function(oldValue) {
      var isNumeric = !isNaN(self.value);
      var isString = Object.prototype.toString.call(self.value) === '[object String]';
      if (isNumeric) {
        if (Math.abs(oldValue - self.value) >= 0.001) {
          methods.updateDom.call(ctx);
        }
      } else if (isString) {
        if (oldValue !== self.value) {
          methods.updateDom.call(ctx);
        }
      }
    };
}
Property.alreadyComputed = [];
Property.isComputing = false;
Property.uniqueId = 0;
Property.postComputeHooks = {};
Property.beginComputing = function() {
  Property.isComputing = true;
};
Property.endComputing = function() {
  for (var i = 0; i < Property.alreadyComputed.length; i++) {
      Property.alreadyComputed[i].isAlreadyComputed = false;
  }
  Property.alreadyComputed = [];
  Property.isComputing = false;
};

/* End eq/js/property.js*/

/* Begin eq/js/init.js*/

// Set up a namespace to contain new objects. Used to avoid namespace
// collisions between libraries.
var eqEd = eqEd || {};

var getInternetExplorerVersion = function()
{
  var rv = -1;
  if (navigator.appName == 'Microsoft Internet Explorer')
  {
    var ua = navigator.userAgent;
    var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
    if (re.exec(ua) != null)
      rv = parseFloat( RegExp.$1 );
  }
  else if (navigator.appName == 'Netscape')
  {
    var ua = navigator.userAgent;
    var re  = new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})");
    if (re.exec(ua) != null)
      rv = parseFloat( RegExp.$1 );
  }
  return rv;
}

var getChromeVersion = function() {
  if (window.navigator.appVersion.match(/Chrome\/(.*?) /) === null) {
    return -1;
  } else {
    return parseInt(window.navigator.appVersion.match(/Chrome\/(\d+)\./)[1], 10);
  }
}

var ChromeVersion = getChromeVersion();
var IEVersion = getInternetExplorerVersion();

// clearHighlighted() will clear all highlighted items on the page.
var clearHighlighted = function () {
    var isHighlighted;
    if (window.getSelection) {
        isHighlighted = (window.getSelection().toString().length > 0);
    } else if (document.selection && document.selection.type != "Control") {
        // To accommodate IE prior to IE9
        isHighlighted = (document.selection.createRange().text.length > 0);
    }
    //If text somehow gets selected, clear it on mouse move
    if (isHighlighted) {
        if (window.getSelection) {
            if (window.getSelection().empty) { // Chrome
                window.getSelection().empty();
            } else if (window.getSelection().removeAllRanges) { // Firefox
                window.getSelection().removeAllRanges();
            }
        } else if (document.selection) { // IE
            if (document.selection.empty) {
                document.selection.empty();
            }
        }
    }
}

jQuery.fn.insertAt = function(index, element) {
    var lastIndex = this.children().size();
    if (index < 0) {
        index = Math.max(0, lastIndex + 1 + index);
    }
    this.append(element);
    if (index < lastIndex) {
        this.children().eq(index).before(this.children().last());
    }
    return this;
};

$.getCSS = function(fromClass, prop) {

    var $inspector = $("<div>").css('display', 'none').addClass(fromClass);
    $("body").append($inspector); // add to DOM, in order to read the CSS property
    try {
        return $inspector.css(prop);
    } finally {
        $inspector.remove(); // and remove from DOM
    }
};

Array.prototype.max = function() {
    return Math.max.apply( Math, this );
};

Array.prototype.getMaxIndex = function() {
    var maxIndex = 0;
    for (var i = 1; i < this.length; i++) {
        if (this[i] > this[maxIndex]) {
            maxIndex = i;
        }
    }
    return maxIndex;
}

Array.prototype.getMinIndex = function() {
    var minIndex = 0;
    for (var i = 1; i < this.length; i++) {
        if (this[i] < this[minIndex]) {
            minIndex = i;
        }
    }
    return minIndex;
}

Array.prototype.contains = function(value) {
  return this.indexOf(value) > -1;
}

var randomIntFromInterval = function(min,max)
{
    return Math.floor(Math.random()*(max-min+1)+min);
}

var insertNodeAtCursor = function(node) {
    var sel, range, html;
    if (window.getSelection) {
        sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
            sel.getRangeAt(0).insertNode(node);
        }
    } else if (document.selection && document.selection.createRange) {
        range = document.selection.createRange();
        html = (node.nodeType == 3) ? node.data : node.outerHTML;
        range.pasteHTML(html);
    }
}

var initializePropertyHooks = function() {
  // Set up some general rules for computing property values.
  Property.postComputeHooks['width'] = function(value) {
    if (typeof value === "undefined" || value === null) {
      value = 0;
    }
    var fontHeight = this.getFontHeight();
    return value + (this.padLeft + this.padRight) * fontHeight;
  };
  Property.postComputeHooks['height'] = function(value) {
    if (typeof value === "undefined" || value === null) {
      value = 0;
    }
    var fontHeight = this.getFontHeight();
    return value + (this.padTop + this.padBottom) * fontHeight;
  };
  Property.postComputeHooks['left'] = function(value) {
    if (typeof value === "undefined" || value === null) {
      value = 0;
    }

    var fontHeight = this.getFontHeight();
    var parentFontHeight = this.parent.getFontHeight();
    // Don't want to add parent's padLeft for a Wrapper,
    // because the definition of Wrapper.left checks the
    // left value of immediately preceding wrapper.left
    // value.
    var additionalLeft = 0;
    if (this instanceof eqEd.Wrapper) {
      additionalLeft = this.adjustLeft * fontHeight;
    } else {
      additionalLeft = this.parent.padLeft * parentFontHeight + this.adjustLeft * fontHeight;
    }
    return value + additionalLeft;
  };
  Property.postComputeHooks['top'] = function(value) {
    if (typeof value === "undefined" || value === null) {
      value = 0;
    }
    var fontHeight = this.getFontHeight();
    return value + (this.parent.padTop + this.adjustTop) * fontHeight;
  };
  Property.postComputeHooks['topAlign'] = function(value) {
    if (typeof value === "undefined" || value === null) {
      value = 0;
    }
    var fontHeight = this.getFontHeight();
    return value + this.padTop * fontHeight;
  };
  Property.postComputeHooks['bottomAlign'] = function(value) {
    if (typeof value === "undefined" || value === null) {
      value = 0;
    }
    var fontHeight = this.getFontHeight();
    return value + this.padBottom * fontHeight;
  };
  Property.postComputeHooks['all'] = function(value, propName) {
    var isNumeric = (value !== null) && !isNaN(value) && !(value === true || value === false) && Object.prototype.toString.call(value) !== '[object Array]';
    if (isNumeric && propName !== "padLeft" && propName !== "padRight"
      && propName !== "padTop" && propName !== "padBottom" 
      && propName !=="adjustTop" && propName !== "adjustLeft" 
      && propName !== "heightRatio" && propName !== "accentGap"
      && propName !== "borderWidth") {
      value = Math.ceil(value);
    }
    return value;
  };

};

/* End eq/js/init.js*/

/* Begin eq/js/fontMetrics.js*/

eqEd.FontMetrics = function() {
    this.fontSizes = ["fontSizeSmallest", "fontSizeSmaller", "fontSizeNormal"];
    this.fontStyles = ["MathJax_MathItalic", "MathJax_Main", "MathJax_MainItalic", "MathJax_Size1", "MathJax_Size2","MathJax_Size3", "MathJax_Size4"];
    // Lists all characters which need to be rendered in a normal font.
    this.MathJax_Main = ['0', '1', '2', '3', '4', '5', '6', '7', '8', 
                 '9', '−', '÷', '⋅', '≈', '*',
                 '-', '=', '+', '/', '<', '>', '≤', 
                 '≥', '∞', '%', '!', '$', '.', '(', ')', '[', 
                 ']', '{', '}', '∂', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 
                 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 
                 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A',
                 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
                 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
                 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '→', '^',
                 '˙', 'Γ', 'Δ', 'Θ', 'Λ', 'Ξ',
                  'Π', 'Σ', 'Υ', 'Φ', 'Ψ',
                  'Ω', '∈', '⃗', '¯', '◦',
                  '×', '±', '∧', '∨', '∖',
                  '≡', '≅', '≠', '∼', '∝',
                  '≺', '⪯', '⊂', '⊆', '≻',
                  '⪰', '⊥', '∣', '∥', ':',
                  '′', ','];
    this.MathJax_MainItalic = ['ı', 'ȷ'];
    // Lists all characters which need to be rendered in an italic font. 
    this.MathJax_MathItalic = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 
                 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 
                 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A',
                 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
                 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
                 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'Γ', 
                 'Δ', 'Θ', 'Λ', 'Ξ', 'Π', 
                 'Σ', 'Υ', 'Φ', 'Ψ', 'Ω', 
                 'α', 'β', 'γ', 'δ', 'ε', 
                 'ϵ', 'ζ', 
                 'η', 'θ', 'ϑ','ι', 'κ', 'λ', 
                 'μ', 'ν', 'ξ', 'π', 'ϖ','ρ', 'ϱ', 
                 'σ', 'ς','τ', 'υ', 'φ', 'ϕ', 'χ', 
                 'ψ', 'ω', 'ς', '\''];
    this.MathJax_Size1 = [];
    this.MathJax_Size2 = [];
    this.MathJax_Size3 = ['(', ')', '{', '}', '[', ']'];
    this.MathJax_Size4 = ['(', ')', '{', '}', '[', ']', '⎛',
                          '⎜', '⎝', '⎞', '⎟',
                          '⎠', '⎧', '⎪', '⎨', 
                          '⎩', '⎫', '⎪', '⎬', 
                          '⎭', '⎡', '⎢', '⎣', 
                          '⎤', '⎥', '⎦'];
    // This will be the union of the characters in each font style.
    // Can be used to see if a character is allowed to be part of an equation.
    this.character = [];
    this.shortCharacters = ['a', 'c', 'e', 'g', 'ı', 'ȷ', 'm',
                            'n', 'o', 'p', 'q', 'r', 's', 'u', 'v', 'w', 'x', 'y', 'z',
                            'α', 'γ', 'ε', 'ϵ', 'η', 'ι',
                            'κ', 'μ', 'ν', 'π', 'ϖ', 'ρ',
                            'ϱ', 'σ', 'ς','τ', 'υ', 'φ', 
                            'χ', 'ω'];
    this.mediumCharacters = ['i', 'j','t'];
    this.tallCharacters = ['b', 'd', 'f', 'h', 'k', 'l', 'A', 'B', 'C', 'D', 'E', 'F',
                           'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
                           'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'β', 'δ',
                           'ζ', 'θ', 'ϑ', 'λ', 'ξ', 'ϕ',
                           'ψ', 'Γ', 'Δ', 'Θ', 'Λ', 'Ξ',
                           'Π', 'Σ', 'Υ', 'Φ', 'Ψ', 'Ω'];
    this.charWidthExceedsBoundingBox = {
        'b': 0.01,
        'c': 0.01,
        'd': 0.01,
        'f': 0.15,
        'g': 0.01,
        'q': 0.075,
        'y': 0.01,
        'z': 0.01,
        'B': 0.01,
        'C': 0.0875,
        'E': 0.0875,
        'F': 0.2,
        'H': 0.1,
        'I': 0.175,
        'J': 0.15,
        'K': 0.075,
        'M': 0.075,
        'N': 0.125,
        'P': 0.175,
        'R': 0.02,
        'S': 0.04,
        'T': 0.25,
        'U': 0.15,
        'V': 0.33,
        'W': 0.13,
        'X': 0.075,
        'Y': 0.33,
        'Z': 0.08,
        'β': 0.05,
        'γ': 0.05,
        'δ': 0.05,
        'ζ': 0.075,
        'η': 0.05,
        'θ': 0.025,
        'ν': 0.1,
        'ξ': 0.025,
        'π': 0.025,
        'ϖ': 0.025,
        'ρ': 0.025,
        'ϱ': 0.025,
        'σ': 0.015,
        'ς': 0.15,
        'τ': 0.185,
        'υ': 0.015,
        'ψ': 0.015,
        'ı': 0.15,
        'ȷ': 0.15
        //'∂': 0.05
    };
    this.height = {
        "fontSizeMessage": parseInt($.getCSS('fontSizeMessage', "font-size"), 10),
        "fontSizeSmallest": parseInt($.getCSS('fontSizeSmallest', "font-size"), 10), 
        "fontSizeSmaller": parseInt($.getCSS('fontSizeSmaller', "font-size"), 10),
        "fontSizeNormal": parseInt($.getCSS('fontSizeNormal', "font-size"), 10)
    };
    // Eventual format will be this.width[character][fontStyle][fontSize]
    this.width = {};

    // Each test div gets its own unique identifier, so font metrics for multiple equations can be calculated simultaneously.
    this.testDivId = randomIntFromInterval(1, 10000);

    //Initialize the object.
    this.computeSymbolSizes();
};

(function() {

    eqEd.FontMetrics.prototype.addTestDiv = function() {
      $('body').append('<div id="testDiv' + this.testDivId + '" class="testEquation eqEdEquation"></div>');
    }

    eqEd.FontMetrics.prototype.removeTestDiv = function() {
      $('#testDiv' + this.testDivId).remove();
    }

    eqEd.FontMetrics.prototype.computeSymbolSizes = function() {
        //  This method will compute the heights and widths for each available character
        //  at each available font size, and font style and store them in the eqEd.FontMetrics
        //  object.  This method should get called on the initialization of the editor. The
        //  purpose of this, is to allow all formatting calculation to be done in pure javascript
        //  after initialization is complete. This saves constantly dipping into the dom to
        //  check the heights widths of characters/containers/wrappers etc. Makes the code
        //  cleaner, and should give a performance boost.
        this.addTestDiv();
        for (var i = 0; i < this.fontStyles.length; i++) {
            var fontStyle = this.fontStyles[i];
            for (var j = 0; j < this[fontStyle].length; j++) {
                var character = this[fontStyle][j];
                this.character.push(character);
                for (var k = 0; k < this.fontSizes.length; k++) {
                    var fontSize = this.fontSizes[k];
                    $('#testDiv' + this.testDivId).append('<div class="' + fontSize + ' ' + fontStyle + ' fontTest" id="fontTest">' + character + '</div>');
                    var fontTest = $('#fontTest');
                    if (typeof this.width[character] === "undefined") {
                        this.width[character] = {};
                    }
                    if (typeof this.height[character] === "undefined") {
                        this.height[character] = {};
                    }
                    if (typeof this.width[character][fontStyle] === "undefined") {
                        this.width[character][fontStyle] = {};
                    }
                    if (typeof this.height[character][fontStyle] === "undefined") {
                        this.height[character][fontStyle] = {};
                    }
                    var extraWidth = (typeof this.charWidthExceedsBoundingBox[character] !== "undefined") ? this.charWidthExceedsBoundingBox[character] : 0;
                    this.width[character][fontStyle][fontSize] = fontTest.outerWidth() * (1 + extraWidth);
                    fontTest.remove();
                }
            }
        }
        this.removeTestDiv();
    }
})();

/* End eq/js/fontMetrics.js*/

/* Begin eq/js/equation-components/misc/equationComponent.js*/

// All objects in the equation editor will inherit
// from EquationComponent. This is where methods/properties
// should be set that will be common to all objects in
// the editor.
eqEd.EquationComponent = function() {
    this.className = "eqEd.EquationComponent";
    // Every component has a reference to the root equation object.
    // This is the top level object that contains properties
    // shared by every child of the equation.
    this.equation = null;
    // Every component has a parent (except for the very top
    // level container, which will have a parent value of
    // null). The parent of a wrapper will be a container.
    // The parent of a container will be a wrapper. The
    // parent of some other object (say the horizontal 
    // bar in a fraction), will be the wrapper that 
    // contains it.
    this.parent = null;
    // The properties array is required for use with Property
    // class. Allows for automatic resolution of dependencies
    // during formatting loop.
    this.properties = [];
    // All components will have left, top, width, height props,
    // in the class to be actually instantiated, will want
    // to create new Property() to represent these values,
    // because allows for a tying to the DOM.
    this.left = 0;
    this.top = 0;
    this.width = 0;
    this.height = 0;
    // When calculating width, padding will be included
    // as part of the width.
    this.padLeft = 0;
    this.padRight = 0;
    this.padTop = 0;
    this.padBottom = 0;
    // Any adjust variable will affect the absolute
    // placement on the page, but will not affect
    // width calculation.
    this.adjustLeft = 0;
    this.adjustTop = 0;
    // Each object will have a corresponding object residing
    // in the Dom. To populate this property, call
    // this.buildDomObj().
    this.domObj = null;
    // This is an array for children of an equation
    // component. Some objects, like containers, and wrappers
    // have their own unique children arrays. This is a default
    // children array for non-wrappers/containers.
    this.children = [];
};

(function() {
    eqEd.EquationComponent.prototype.constructor = eqEd.EquationComponent;
    // Each component must have a definition for clone,
    // because will need deep clones of equations for
    // copy/cut, paste mechanisms.
    eqEd.EquationComponent.prototype.clone = function() {
        return new this.constructor();
    };
    // Use buildDomObj() to create an instance of
    // equationDom.
    eqEd.EquationComponent.prototype.buildDomObj = function() {};
    // update() will recursively call compute() on
    // nested objects while making sure all depencencies
    // are resolved in the correct order. Requires the
    // Property class. Also takes care of updating Dom
    // objects that correspond to properties being
    // computed.
    eqEd.EquationComponent.prototype.update = function() {
        for (var i = 0; i < this.properties.length; i++) {
            this.properties[i].compute();
        }
        for (var i = 0; i < this.children.length; i++) {
            this.children[i].update();
        }
    };
    // updateAll allows formatting the entire equation
    // that some object belongs to without having a
    // reference to the root node.
    eqEd.EquationComponent.prototype.updateAll = function() {
        // Do some set up in the static Property object
        // to allow for scanning of compute() methods
        // to determine dependencies dynamically.
        Property.beginComputing();
        
        // This line kicks off the recursive formatting cycle.
        var rootElement = null;
        if (this instanceof eqEd.Equation) {
            rootElement = this;
        } else {
            rootElement = this.equation;
        }
        rootElement.update();

        // Do some clean up for the static Property object.
        // This will allow for a new recursive update cycle
        // to occur correctly in the future.
        Property.endComputing();
    };
    // If this is called during a compute() call, should
    // call compute on fontSize  properties that are
    // encountered.
    eqEd.EquationComponent.prototype.getFontHeight = function() {
        var context = this;
        while (typeof context.fontSize === "undefined") {
          context = context.parent;
        }
        var rootElement = null;
        if (this instanceof eqEd.Equation) {
            rootElement = this;
        } else {
            rootElement = this.equation;
        }
        return rootElement.fontMetrics.height[context.fontSize];
    }
})();

/* End eq/js/equation-components/misc/equationComponent.js*/

/* Begin eq/js/equation-components/misc/boundEquationComponent.js*/

// This class can only be inherited from. It should not be instantiated.
// Bound equation components are always tied to their parent wrapper.
eqEd.BoundEquationComponent = function(parent) {
  eqEd.EquationComponent.call(this); // call super constructor.
  this.className = "eqEd.BoundEquationComponent";
  this.parent = parent;
  this.equation = (parent === null) ? null : parent.equation;
};

(function() {
    // subclass extends superclass
    eqEd.BoundEquationComponent.prototype = Object.create(eqEd.EquationComponent.prototype);
    eqEd.BoundEquationComponent.prototype.clone = function() {
        return new this.constructor(this.parent);
    };
})();

/* End eq/js/equation-components/misc/boundEquationComponent.js*/

/* Begin eq/js/equation.js*/

eqEd.Equation = function() {
    eqEd.EquationComponent.call(this); // call super constructor.

    this.className = "eqEd.Equation";
    // FontMetrics gives some info about fontHeights.
    // Allows all calculations to happen with plain javascript
    // objects without accessing the DOM.
    this.fontMetrics = new eqEd.FontMetrics();
    // TODO: Fix this. It shouldn't be so verbose. (Maybe make a new class that contains all of this.)
    this.topLevelContainer = new eqEd.TopLevelContainer(this);
    /*
    this.topLevelContainer.equation = this;
    this.topLevelContainer.padTop = 0.2;
    this.topLevelContainer.padBottom = 0.2;
    this.topLevelContainer.fontSize = "fontSizeNormal";
    this.topLevelContainer.domObj = this.topLevelContainer.buildDomObj();
    this.topLevelContainer.domObj.updateFontSize(this.topLevelContainer.fontSize);
    var topLevelEmptyContainerWrapper = new eqEd.TopLevelEmptyContainerWrapper(this);
    this.topLevelContainer.addWrappers([0, topLevelEmptyContainerWrapper]);
    */
    this.domObj = this.buildDomObj();
    this.domObj.append(this.topLevelContainer.domObj);

    this.fontSize = "fontSizeNormal";
    this.children = [this.topLevelContainer];
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
    get: function() {
      return width;
    },
    set: function(value) {
      width = value;
    },
    compute: function() {
      return this.topLevelContainer.width;
    },
    updateDom: function() {
        this.domObj.updateWidth(this.width);
    }
    }));

    var height = 0;
    this.properties.push(new Property(this, "height", height, {
    get: function() {
      return height;
    },
    set: function(value) {
      height = value;
    },
    compute: function() {
      return this.topLevelContainer.height;
    },
    updateDom: function() {
        this.domObj.updateHeight(this.height);
    }
    }));
};

(function() {
    eqEd.Equation.prototype = Object.create(eqEd.EquationComponent.prototype);
    eqEd.Equation.prototype.constructor = eqEd.Equation;
    eqEd.Equation.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="eqEdEquation"></div>')
    };
    eqEd.Equation.prototype.clone = function() {
        return new this.constructor();
    };
    eqEd.Equation.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: "Equation",
            value: null,
            operands: {
                topLevelContainer: this.topLevelContainer.buildJsonObj()
            }
        };
        return jsonObj;
    };
    eqEd.Equation.constructFromJsonObj = function(jsonObj) {
        var equation = new eqEd.Equation();
        for (var i = 0; i < jsonObj.operands.topLevelContainer.length; i++) {
            var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.topLevelContainer[i].type);
            var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.topLevelContainer[i], equation);
            equation.topLevelContainer.addWrappers([i, innerWrapper]);
        }
        return equation;
    };
    eqEd.Equation.JsonTypeToConstructor = function(type) {
        var typeToConstructorMapping = {
            'Accent': eqEd.AccentWrapper,
            'BigOperator': eqEd.BigOperatorWrapper,
            'BracketPair': eqEd.BracketPairWrapper,
            'Bracket': eqEd.BracketWrapper,
            'Equation': eqEd.Equation,
            'FunctionLower': eqEd.FunctionLowerWrapper,
            'Function': eqEd.FunctionWrapper,
            'Integral': eqEd.IntegralWrapper,
            'Limit': eqEd.LimitWrapper,
            'LogLower': eqEd.LogLowerWrapper,
            'Matrix': eqEd.MatrixWrapper,
            'NthRoot': eqEd.NthRootWrapper,
            'Operator': eqEd.OperatorWrapper,
            'SquareRoot': eqEd.SquareRootWrapper,
            'StackedFraction': eqEd.StackedFractionWrapper,
            'Subscript': eqEd.SubscriptWrapper,
            'SuperscriptAndSubscript': eqEd.SuperscriptAndSubscriptWrapper,
            'Superscript': eqEd.SuperscriptWrapper,
            'Symbol': eqEd.SymbolWrapper
        }
        return typeToConstructorMapping[type];
    };
})();

/* End eq/js/equation.js*/

/* Begin eq/js/equation-components/dom/equationDom.js*/

eqEd.EquationDom = function(binding, html) {
    this.className = "eqEd.EquationDom";
    this.binding = binding;
    this.html = html;
    this.value = $(html);
    this.value.data("eqObject", this.binding);
    this.width = 0;
    this.height = 0;
    this.left = 0;
    this.top = 0;

    this.value.attr("contenteditable", false);
    this.value.attr("autocomplete", "off");
    this.value.attr("autocorrect", "off");
    this.value.attr("autocapitalize", "off");
    this.value.attr("spellcheck", false);
    this.value.focus();
    this.value.blur();
}
eqEd.EquationDom.prototype.constructor = eqEd.EquationDom;
eqEd.EquationDom.prototype.updateWidth = function(width) {
    this.width = width;
    this.value.css('width', width + 'px');
}
eqEd.EquationDom.prototype.updateHeight = function(height) {
    this.height = height;
    this.value.css('height', height + 'px');
}
eqEd.EquationDom.prototype.updateLeft = function(left) {
    this.left = left;
    this.value.css('left', left + 'px');
}
eqEd.EquationDom.prototype.updateTop = function(top) {
    this.top = top;
    this.value.css('top', top + 'px');
}
eqEd.EquationDom.prototype.clone = function() {
    var copy = new eqEd.EquationDom(this.binding, this.html);
    copy.updateWidth(this.width);
    copy.updateHeight(this.height);
    copy.updateLeft(this.left);
    copy.updateTop(this.top);
	return copy;
}
eqEd.EquationDom.prototype.append = function(domObject) {
	this.value.append(domObject.value);
}

eqEd.EquationDom.prototype.empty = function() {
    this.value.empty();
}

eqEd.EquationDom.prototype.addClass = function(className) {
    this.value.addClass(className);
}

eqEd.EquationDom.prototype.updateFontSize = function(fontClass) {
    this.value.removeClass('fontSizeNormal');
    this.value.removeClass('fontSizeSmaller');
    this.value.removeClass('fontSizeSmallest');
    this.value.addClass(fontClass);
}

eqEd.EquationDom.prototype.updateBorderWidth = function(borderWidth) {
    this.value.css('border-width', borderWidth + 'px');
}

/* End eq/js/equation-components/dom/equationDom.js*/

/* Begin eq/js/equation-components/containers/container.js*/

// The container class will define a "scope" in the
// editor. Containers consist of wrappers lined up
// in a row. A container may be within a wrapper
// (example: numerator in a fraction; numerator 
// defines a new scope).
eqEd.Container = function(parent) {
  eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
  this.className = "eqEd.Container";
  
  // The wrappers property defines the wrapper objects
  // that are within this container object. Wrapper
  // objects will be formatted in the order that they
  // exist in this array.
  this.wrappers = [];

  // fontSize can be one of three predefined values,
  // fontSizeNormal, fontSizeSmaller, or fontSizeSmallest.
  // The actual height of these font sizes will be defined
  // in the CSS file. This property will try to follow
  // LaTeX conventions for sizing in relation to nesting.
  // Will need to create new Property() for 
  this.fontSize = "";
  
  var maxTopAlignIndex = null;
  this.properties.push(new Property(this, "maxTopAlignIndex", maxTopAlignIndex, {
    get: function() {
      return maxTopAlignIndex;
    },
    set: function(value) {
      maxTopAlignIndex = value;
    },
    compute: function() {
      var maxIndex = 0;
      for (var i = 1; i < this.wrappers.length; i++) {
        if (this.wrappers[i].topAlign > this.wrappers[maxIndex].topAlign) {
            maxIndex = i;
        }
      }
      return (this.wrappers.length === 0) ? null : maxIndex;
    },
    updateDom: function() {}
  }));
  var maxBottomAlignIndex = null;
  this.properties.push(new Property(this, "maxBottomAlignIndex", maxBottomAlignIndex, {
    get: function() {
      return maxBottomAlignIndex;
    },
    set: function(value) {
      maxBottomAlignIndex = value;
    },
    compute: function() {
      var maxIndex = 0;
      for (var i = 1; i < this.wrappers.length; i++) {
        if (this.wrappers[i].bottomAlign > this.wrappers[maxIndex].bottomAlign) {
            maxIndex = i;
        }
      }
      return (this.wrappers.length === 0) ? null : maxIndex;
    },
    updateDom: function() {}
  }));
  var width = 0;
  this.properties.push(new Property(this, "width", width, {
    get: function() {
      return width;
    },
    set: function(value) {
      width = value;
    },
    compute: function() {
      var sum = 0;
      for (var i = 0; i < this.wrappers.length; i++) {
        sum += this.wrappers[i].width;
      }
      return sum;
    },
    updateDom: function() {
        this.domObj.updateWidth(this.width);
    }
  }));
  var height = 0;
  this.properties.push(new Property(this, "height", height, {
    get: function() {
      return height;
    },
    set: function(value) {
      height = value;
    },
    compute: function() {
      if (this.wrappers.length > 0) {
        return this.wrappers[this.maxTopAlignIndex].topAlign + this.wrappers[this.maxBottomAlignIndex].bottomAlign;
      } else {
        return 0;
      }
      
    },
    updateDom: function() {
        this.domObj.updateHeight(this.height);
    }
  }));

  var clipping = "";
  this.properties.push(new Property(this, "clipping", clipping, {
    get: function() {
      return clipping;
    },
    set: function(value) {
      clipping = value;
    },
    compute: function() {
      // adding 2 px b/c j-hat bottom gets cut off.
      return '0px ' + (this.width + 5) + 'px ' + (height + 2) + 'px ' + (-5) + 'px';
    },
    updateDom: function() {
        this.domObj.updateClipping(this.clipping);
    }
  }));
};
(function() {
    // subclass extends superclass
    eqEd.Container.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.Container.prototype.constructor = eqEd.Container;
    eqEd.Container.prototype.updateWrapperProperties = function() {
      // Update the index
      for (var i = 0; i < this.wrappers.length; i++) {
          this.wrappers[i].index = i;
          this.wrappers[i].parent = this;
          this.wrappers[i].equation = this.equation;
      }
    }

    eqEd.Container.prototype.addWrappers = function(indexAndWrapperList) {
      // This method takes a list of indices, and wrapper objects, and adds them
      // to the container object's list of wrappers.
      // The indices should specify what the final desired index of the wrapper
      // object should be. Wrappers already in the list (at indices greater than 
      // the inserted wrappers index) will be pushed to the right one entry for 
      // each wrapper inserted. Order of the index/wrapper pairs as arguments 
      // shouldn't matter.
      // e.g. container.addWrappers([1, wrapper1], [4, wrapper3], [2, wrapper2]);
      // If the original list was [w0, w1, w2, w3, w4, w5, w6],
      // it would now be [w0, wrapper1, wrapper2, w1, wrapper3, w2, w3, w4, w5, w6]
      indexAndWrapperList = Array.prototype.slice.call(arguments);
      indexAndWrapperList = _.sortBy(indexAndWrapperList, function(innerArr) {
          return innerArr[0];
      });
      if (this.wrappers[0] instanceof eqEd.EmptyContainerWrapper || this.wrappers[0] instanceof eqEd.SquareEmptyContainerFillerWrapper) {
        this.removeWrappers(0);
      }

      // Insert the wrapper objects into this container's wrapper array, and add
      // them to the DOM.
      for (var i = 0; i < indexAndWrapperList.length; i++) {
          var index = indexAndWrapperList[i][0];
          var wrapper = indexAndWrapperList[i][1];
          // Insert the wrapper object into this.wrappers.
          this.wrappers.splice(index, 0, wrapper);
          // Insert the wrapper's dom object into this container's
          // dom object.
          this.domObj.addWrapper(index, wrapper);
      }
      // This call corrects the indices/parent values of this container's
      // wrappers.
      this.updateWrapperProperties();
    }

    eqEd.Container.prototype.removeWrappers = function(indexList) {
      // indexList is just a list of indices to be removed.
      // this will remove the wrappers at the indices when this
      // method call is made.  Offsetting is handled by the 
      // correction variable in the deleting loop.
      indexList = Array.prototype.slice.call(arguments);

      var maxIndex = indexList[indexList.getMaxIndex()];
      var minIndex = indexList[indexList.getMinIndex()];

      var correction = 0;
      for (var i = 0; i < indexList.length; i++) {
          // Remove the wrapper object into this.wrappers.
          this.wrappers.splice(indexList[i] - correction, 1);
          // Remove the wrapper's dom object from this container's
          // dom object.
          this.domObj.removeWrapper(indexList[i] - correction);
          correction += 1;
      }-
      // This call corrects the indices/parent values of this container's
      // wrappers.
      this.updateWrapperProperties();
    }
    eqEd.Container.prototype.update = function() {
      // This first for loop is what does the actual computing
      // of the properties for this object.  It will also recursively
      // resolve all of the dependencies required to compute the 
      // properties in this object.
      
      for (var i = 0; i < this.properties.length; i++) {
        this.properties[i].compute();
      }
      // This loop will now recursively through the equation,
      // ensuring that all connected objects in the equation will
      // have their compute() methods called and Dom updated.
      // An object could have a property that is not a dependency 
      // of the properties on this object. That is why this recursive
      // call is required.
      for (var i = 0; i < this.wrappers.length; i++) {
          this.wrappers[i].update();
      }
    }
    // TODO Write tests for clone!
    eqEd.Container.prototype.clone = function() {
      var copy = new this.constructor(this.parent);
      var indexAndWrapperList = [];
      for (var i = 0; i < this.wrappers.length; i++) {
        indexAndWrapperList.push([i, this.wrappers[i].clone()]);
      }
      eqEd.Container.prototype.addWrappers.apply(copy, indexAndWrapperList);
      return copy;
    }
    eqEd.Container.prototype.buildJsonObj = function() {
      var jsonWrappers = [];
      if (!(this.wrappers[0] instanceof eqEd.EmptyContainerWrapper)) {
        for (var i = 0; i < this.wrappers.length; i++) {
          jsonWrappers.push(this.wrappers[i].buildJsonObj());
        }
      }
      return jsonWrappers;
    }
    eqEd.Container.prototype.buildDomObj = function() {
      return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer"></div>');
    }
})();

/* End eq/js/equation-components/containers/container.js*/

/* Begin eq/js/equation-components/dom/containerDom.js*/

eqEd.ContainerDom = function(binding, html) {
  eqEd.EquationDom.call(this, binding, html); // call super constructor.
  this.className = "eqEd.ContainerDom";
}
// subclass extends superclass
eqEd.ContainerDom.prototype = Object.create(eqEd.EquationDom.prototype);
eqEd.ContainerDom.prototype.constructor = eqEd.ContainerDom;
eqEd.ContainerDom.prototype.addWrapper = function(index, wrapper) {
    this.value.insertAt(index, wrapper.domObj.value);
}
eqEd.ContainerDom.prototype.removeWrapper = function(index) {
    this.value.children().eq(index).remove();
}

eqEd.ContainerDom.prototype.updateClipping = function(clipping) {
	this.value.css('clip', 'rect(' + clipping + ')');
}

/* End eq/js/equation-components/dom/containerDom.js*/

/* Begin eq/js/equation-components/wrappers/wrapper.js*/

eqEd.Wrapper = function(equation) {
    eqEd.EquationComponent.call(this); // call super constructor.
    this.className = "eqEd.Wrapper";
    
    this.equation = equation;

    this.topAlign = 0;
    this.bottomAlign = 0;
    this.index = null;
    this.childContainers = [];
    this.childNoncontainers = [];

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // special code added to prevent compute hook from
            // executing on this property.
            var leftVal = 0;
            if (this.index === 0) {
                leftVal = this.parent.padLeft;
            } else {
                var prevWrapper = this.parent.wrappers[this.index - 1];
                leftVal = prevWrapper.left + prevWrapper.width;
            }
            return leftVal;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            return this.parent.wrappers[this.parent.maxTopAlignIndex].topAlign - this.topAlign;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return this.topAlign + this.bottomAlign;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.Wrapper.prototype = Object.create(eqEd.EquationComponent.prototype);
    eqEd.Wrapper.prototype.constructor = eqEd.Wrapper;
    eqEd.Wrapper.prototype.update = function() {
        // This first for loop is what does the actual computing
        // of the properties for this object.  It will also recursively
        // resolve all of the dependencies required to compute the 
        // properties in this object.
        for (var i = 0; i < this.properties.length; i++) {
            this.properties[i].compute();
        }
        // This loop will now recursiely through the equation,
        // ensuring that all connected objects in the equation will
        // have their compute() methods called and Dom updated.
        // An object could have a property that is not a dependency 
        // of the properties on this object. That is why this recursive
        // call is required.
        for (var i = 0; i < this.childContainers.length; i++) {
            this.childContainers[i].update();
        }
        for (var i = 0; i < this.childNoncontainers.length; i++) {
            this.childNoncontainers[i].update();
        }
    }
    eqEd.Wrapper.prototype.buildJsonObj = function() {}
    // Each wrapper class will need it's own clone() definition
    eqEd.Wrapper.prototype.clone = function() {};
})();

/* End eq/js/equation-components/wrappers/wrapper.js*/

/* Begin eq/js/equation-components/dom/wrapperDom.js*/

eqEd.WrapperDom = function(binding, html) {
  eqEd.EquationDom.call(this, binding, html); // call super constructor.
  this.className = "eqEd.WrapperDom";
}
// subclass extends superclass
eqEd.WrapperDom.prototype = Object.create(eqEd.EquationDom.prototype);
eqEd.WrapperDom.prototype.constructor = eqEd.WrapperDom;

/* End eq/js/equation-components/dom/wrapperDom.js*/

/* Begin eq/js/equation-components/misc/symbol.js*/

eqEd.Symbol = function(parent, character, fontStyle) {
    eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
    this.className = "eqEd.Symbol";
    
    this.character = character;
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    
    if (IEVersion >= 9) {
        if (this.fontStyle === "MathJax_MathItalic") {
            this.adjustTop = 0.340;
        } else {
            this.adjustTop = 0.280;
        }
    } else {
        if (this.fontStyle === "MathJax_MathItalic") {
            this.adjustTop = 0.085
        } else {
            this.adjustTop = 0.025;
        }
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            return this.equation.fontMetrics.width[this.character][this.fontStyle][this.parent.parent.fontSize];
        },
        updateDom: function() {
            // This doesn't really belong here, but it is a convenient callback
            if (this.parent !== null 
                &&this.parent.parent.parent instanceof eqEd.AccentContainer) {
                if (this.character === 'i') {
                    this.character = 'ı';
                    this.domObj = this.buildDomObj();
                } else if (this.character === 'j') {
                    this.character = 'ȷ';
                    this.domObj = this.buildDomObj();
                }
            }
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var fontHeight = this.getFontHeight();
            return 1 * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.Symbol.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.Symbol.prototype.constructor = eqEd.Symbol;
    eqEd.Symbol.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="symbol ' + this.fontStyle + '">' + this.character + '</div>');
    };
})();

/* End eq/js/equation-components/misc/symbol.js*/

/* Begin eq/js/equation-components/wrappers/symbolWrapper.js*/

eqEd.SymbolWrapper = function(equation, character, fontStyle) {
	eqEd.Wrapper.call(this, equation); // call super constructor.
	this.className = "eqEd.SymbolWrapper";

    this.symbol = new eqEd.Symbol(this, character, fontStyle);
	this.domObj = this.buildDomObj();
	this.domObj.append(this.symbol.domObj);
	this.childNoncontainers = [this.symbol];

    

    // Set up the isDifferential calculation
    var isDifferential = 0;
    this.properties.push(new Property(this, "isDifferential", isDifferential, {
        get: function() {
            return isDifferential;
        },
        set: function(value) {
            isDifferential = value;
        },
        compute: function() {
            var isDifferentialVal = false;
            if (this.symbol.character === 'd') {
                if (this.index !== (this.parent.wrappers.length - 1) 
                    && this.parent.wrappers[this.index + 1] instanceof eqEd.SymbolWrapper
                    && this.parent.wrappers[this.index + 1].symbol.character !== 'd') {
                    var integralCount = 0;
                    var differentialCount = 0;
                    for (var i = 0; i < this.index; i++) {
                        var wrapper = this.parent.wrappers[i];
                        if (wrapper instanceof eqEd.IntegralWrapper) {
                            integralCount += wrapper.numIntegrals;
                        } else if (wrapper instanceof eqEd.SymbolWrapper 
                                    && wrapper.symbol.character === 'd'
                                    && this.parent.wrappers[i + 1] instanceof eqEd.SymbolWrapper
                                    && this.parent.wrappers[i + 1].symbol.character !== 'd') {
                                differentialCount++;
                        }
                    }
                    if (integralCount > differentialCount) {
                        isDifferentialVal = true;
                    }
                }
            }
            return isDifferentialVal;
        },
        updateDom: function() {}
    }));

    // Set up the padLeft calculation
    var padLeft = 0;
    this.properties.push(new Property(this, "padLeft", padLeft, {
        get: function() {
            return padLeft;
        },
        set: function(value) {
            padLeft = value;
        },
        compute: function() {
            var padLeftVal = 0;
            // Special padding logic for differentials after integrals.
            if (this.isDifferential) {
                padLeftVal = 0.15;
            }
            if (this.index === 0) {
                //padLeftVal += 0.1;
            }
            return padLeftVal;
        },
        updateDom: function() {}
    }));

    // Set up the padRight calculation
    var padRight = 0;
    this.properties.push(new Property(this, "padRight", padRight, {
        get: function() {
            return padRight;
        },
        set: function(value) {
            padRight = value;
        },
        compute: function() {
            var padRightVal = 0;
            // Special padding logic for differentials after integrals.
            if (this.index !== 0 && this.parent.wrappers[this.index - 1].isDifferential) {
                // At zero for now, but could add padding after differential if I wanted to here.
                padRightVal += 0;
            }
            return padRightVal;
        },
        updateDom: function() {}
    }));

	// Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            return this.symbol.width;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
            return 0.60 * this.symbol.height;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
            return 0.40 * this.symbol.height;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.SymbolWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.SymbolWrapper.prototype.constructor = eqEd.SymbolWrapper;
    eqEd.SymbolWrapper.prototype.clone = function() {
    	return new this.constructor(this.equation, this.symbol.character, this.symbol.fontStyle);
    };
    eqEd.SymbolWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper symbolWrapper"></div>');
    };
    eqEd.SymbolWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: this.symbol.character,
            operands: null
        };
        return jsonObj;
    };
    eqEd.SymbolWrapper.constructFromJsonObj = function(jsonObj, equation) {
        var fontStyle = "";
        if (_.indexOf(equation.fontMetrics.MathJax_MathItalic, jsonObj.value) !== -1) {
            fontStyle = "MathJax_MathItalic";
        } else if (_.indexOf(equation.fontMetrics.MathJax_Main, jsonObj.value) !== -1) {
            fontStyle = "MathJax_Main";
        } else if (_.indexOf(equation.fontMetrics.MathJax_MainItalic, jsonObj.value) !== -1) {
            fontStyle = "MathJax_MainItalic";
        }
        var symbolWrapper = new eqEd.SymbolWrapper(equation, jsonObj.value, fontStyle);
        return symbolWrapper;
    }
})();

/* End eq/js/equation-components/wrappers/symbolWrapper.js*/

/* Begin eq/js/equation-components/wrappers/operatorWrapper.js*/

eqEd.OperatorWrapper = function(equation, operatorSymbol, fontStyle) {
	eqEd.Wrapper.call(this, equation); // call super constructor.
	this.className = "eqEd.OperatorWrapper";

    this.operatorSymbol = operatorSymbol;
    this.operator = new eqEd.Symbol(this, operatorSymbol, fontStyle);
	this.domObj = this.buildDomObj();
	this.domObj.append(this.operator.domObj);
	this.childNoncontainers = [this.operator];

    // Set up the isUnary calculation
    var isUnary = false;
    this.properties.push(new Property(this, "isUnary", isUnary, {
        get: function() {
            return isUnary;
        },
        set: function(value) {
            isUnary = value;
        },
        compute: function() {
            var isUnaryVal = false;
            var i = this.index;
            if ((i === 0 || this.parent.wrappers[i-1] instanceof eqEd.OperatorWrapper || (this.parent.wrappers[i-1] instanceof eqEd.BracketWrapper && this.parent.wrappers[i-1].bracket instanceof eqEd.LeftBracket))
                && (this.operator.character === "+" || this.operator.character === "−")) {
                    isUnaryVal = true;
            }
            return isUnaryVal;
        },
        updateDom: function() {}
    }));

    // Set up the isComparison calculation
    var isComparison = false;
    this.properties.push(new Property(this, "isComparison", isComparison, {
        get: function() {
            return isComparison;
        },
        set: function(value) {
            isComparison = value;
        },
        compute: function() {
            var isComparisonVal = false;
            if (this.operator.character === "="
                       || this.operator.character === "<"
                       || this.operator.character === ">"
                       || this.operator.character === "≤"
                       || this.operator.character === "≥"
                       || this.operator.character === "≈"
                       || this.operator.character === "≡"
                       || this.operator.character === "≅"
                       || this.operator.character === "≠"
                       || this.operator.character === "∼"
                       || this.operator.character === "∝"
                       || this.operator.character === "≺"
                       || this.operator.character === "⪯"
                       || this.operator.character === "⊂"
                       || this.operator.character === "⊆"
                       || this.operator.character === "≻"
                       || this.operator.character === "⪰") {
                isComparisonVal = true;
            }
            return isComparisonVal;
        },
        updateDom: function() {}
    }));

    // Set up the padLeft calculation
    var padLeft = false;
    this.properties.push(new Property(this, "padLeft", padLeft, {
        get: function() {
            return padLeft;
        },
        set: function(value) {
            padLeft = value;
        },
        compute: function() {
            var padLeftVal = 0.15;
            if (this.isComparison) {
                padLeftVal = 0.2;
            } else if (this.isUnary && this.index === 0) {
                padLeftVal = 0;
            }
            return padLeftVal;
        },
        updateDom: function() {}
    }));

    // Set up the padRight calculation
    var padRight = false;
    this.properties.push(new Property(this, "padRight", padRight, {
        get: function() {
            return padRight;
        },
        set: function(value) {
            padRight = value;
        },
        compute: function() {
            var padRightVal = 0;
            if (this.isUnary) {
                padRightVal = 0;
            } else {
                if (this.isComparison) {
                    padRightVal = 0.2;
                } else {
                    padRightVal = 0.15;
                }
            }
            return padRightVal;
        },
        updateDom: function() {}
    }));

	// Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            return this.operator.width;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
            return 0.62 * this.operator.height;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
            return 0.38 * this.operator.height;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.OperatorWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.OperatorWrapper.prototype.constructor = eqEd.OperatorWrapper;
    eqEd.OperatorWrapper.prototype.clone = function() {
    	return new this.constructor(this.equation, this.operatorSymbol, this.operator.fontStyle);
    };
    eqEd.OperatorWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper operatorWrapper"></div>')
    };
    eqEd.OperatorWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: this.operator.character,
            operands: null
        };
        return jsonObj;
    };
    eqEd.OperatorWrapper.constructFromJsonObj = function(jsonObj, equation) {
      var operatorWrapper = new eqEd.OperatorWrapper(equation, jsonObj.value, "MathJax_Main");
      return operatorWrapper;
    }
})();

/* End eq/js/equation-components/wrappers/operatorWrapper.js*/

/* Begin eq/js/equation-components/wrappers/emptyContainerWrapper.js*/

eqEd.EmptyContainerWrapper = function(equation) {
    eqEd.Wrapper.call(this, equation); // call super constructor.
    this.className = "eqEd.EmptyContainerWrapper";
};
(function() {
    // subclass extends superclass
    eqEd.EmptyContainerWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.EmptyContainerWrapper.prototype.constructor = eqEd.EmptyContainerWrapper;
})();

/* End eq/js/equation-components/wrappers/emptyContainerWrapper.js*/

/* Begin eq/js/equation-components/wrappers/topLevelEmptyContainerWrapper.js*/

eqEd.TopLevelEmptyContainerWrapper = function(equation) {
    eqEd.EmptyContainerWrapper.call(this, equation); // call super constructor.
    this.className = "eqEd.EmptyContainerWrapper";

    this.topLevelEmptyContainerMessage = new eqEd.TopLevelEmptyContainerMessage(this);
    this.domObj = this.buildDomObj();
    this.domObj.append(this.topLevelEmptyContainerMessage.domObj);
    this.childNoncontainers = [this.topLevelEmptyContainerMessage];
    this.padLeft = 0;
    this.padRight = 0.5;

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            return this.topLevelEmptyContainerMessage.width;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
            var fontHeight = this.getFontHeight();
            return 0.5 * fontHeight;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
            var fontHeight = this.getFontHeight();
            return 0.5 * fontHeight;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.TopLevelEmptyContainerWrapper.prototype = Object.create(eqEd.EmptyContainerWrapper.prototype);
    eqEd.TopLevelEmptyContainerWrapper.prototype.constructor = eqEd.TopLevelEmptyContainerWrapper;
    eqEd.TopLevelEmptyContainerWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper emptyContainerWrapper topLevelEmptyContainerWrapper"></div>');
    }
})();

/* End eq/js/equation-components/wrappers/topLevelEmptyContainerWrapper.js*/

/* Begin eq/js/equation-components/misc/topLevelEmptyContainerMessage.js*/

eqEd.TopLevelEmptyContainerMessage = function(parent) {
    eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
    this.className = "eqEd.TopLevelEmptyContainerMessage";
    
    this.message = "Enter&nbsp;your&nbsp;equation&nbsp;here&hellip;";
    this.fontSize = "fontSizeMessage";
    this.domObj = this.buildDomObj();
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // not good; jQuery specific function width() in code.
            // wanted to abstract through domObj.
            return this.domObj.value.width();
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // not good; jQuery specific function height() in code.
            // wanted to abstract through domObj.
            return this.domObj.value.height();
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            var fontHeight = this.getFontHeight();
            return 0.5 * (this.parent.height - this.height) - this.parent.padTop * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.TopLevelEmptyContainerMessage.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.TopLevelEmptyContainerMessage.prototype.constructor = eqEd.TopLevelEmptyContainerMessage;
    eqEd.TopLevelEmptyContainerMessage.prototype.clone = function() {
        // character doesn't need cloned, because it isn't an object, it's
        // a native type.  fontMetrics doesn't need cloned, because
        // it is a singleton over the equation life cycle. Only need a 
        // reference to the singleton.
        return new this.constructor();
    };
    eqEd.TopLevelEmptyContainerMessage.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<span class="topLevelEmptyContainerMessage ' + this.fontSize + '">' + this.message + '</span>');
    };
})();

/* End eq/js/equation-components/misc/topLevelEmptyContainerMessage.js*/

/* Begin eq/js/equation-components/wrappers/squareEmptyContainerWrapper.js*/

eqEd.SquareEmptyContainerWrapper = function(equation) {
    eqEd.EmptyContainerWrapper.call(this, equation); // call super constructor.
    this.className = "eqEd.SquareEmptyContainerWrapper";

    this.squareEmptyContainer = new eqEd.SquareEmptyContainer(this);
    this.domObj = this.buildDomObj();
    this.domObj.append(this.squareEmptyContainer.domObj);
    this.childContainers = [this.squareEmptyContainer];

    this.padLeft = 0.05;
    this.padRight = 0.05;

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            return this.squareEmptyContainer.width;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            return 0.5 * fontHeight;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            return 0.5 * fontHeight;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.SquareEmptyContainerWrapper.prototype = Object.create(eqEd.EmptyContainerWrapper.prototype);
    eqEd.SquareEmptyContainerWrapper.prototype.constructor = eqEd.SquareEmptyContainerWrapper;
    eqEd.SquareEmptyContainerWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper emptyContainerWrapper squareEmptyContainerWrapper"></div>')
    }
    eqEd.SquareEmptyContainerWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation);
        copy.squareEmptyContainer = this.squareEmptyContainer.clone();
        copy.squareEmptyContainer.parent = copy;
        copy.domObj = copy.buildDomObj();
        copy.domObj.append(copy.squareEmptyContainer.domObj);
        copy.childContainers = [copy.squareEmptyContainer];
        return copy;
    }
})();

/* End eq/js/equation-components/wrappers/squareEmptyContainerWrapper.js*/

/* Begin eq/js/equation-components/containers/squareEmptyContainer.js*/

eqEd.SquareEmptyContainer = function(parent) {
    eqEd.Container.call(this, parent);
    this.className = "eqEd.SquareEmptyContainer";
    this.borderWidth = 4;
    this.fontSize = "fontSizeNormal";
    this.domObj = this.buildDomObj();

    this.squareEmptyContainerFillerWrapper = new eqEd.SquareEmptyContainerFillerWrapper(this.equation);
    this.addWrappers([0, this.squareEmptyContainerFillerWrapper]);

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.fontSize];
            return 0.5 * fontHeight - 0.5 * this.squareEmptyContainerFillerWrapper.height;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            return actualParentContainer.fontSize;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));

    // Set up the borderWidth calculation
    var borderWidth = 0;
    this.properties.push(new Property(this, "borderWidth", fontSize, {
        get: function() {
            return borderWidth;
        },
        set: function(value) {
            borderWidth = value;
        },
        compute: function() {
            return 0.088 * this.equation.fontMetrics.height["fontSizeNormal"];
        },
        updateDom: function() {
            this.domObj.updateBorderWidth(borderWidth);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.SquareEmptyContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.SquareEmptyContainer.prototype.constructor = eqEd.SquareEmptyContainer;
    eqEd.SquareEmptyContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer squareEmptyContainer ' + this.fontSize + '"></div>');
    };
})();

/* End eq/js/equation-components/containers/squareEmptyContainer.js*/

/* Begin eq/js/equation-components/wrappers/squareEmptyContainerFillerWrapper.js*/

eqEd.SquareEmptyContainerFillerWrapper = function(equation) {
    eqEd.Wrapper.call(this, equation); // call super constructor.
    this.className = "eqEd.SquareEmptyContainerFillerWrapper";

    this.domObj = this.buildDomObj();


    this.sideLength = 0.85;

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            return this.sideLength * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            return 0.5 * this.sideLength * fontHeight;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            return 0.5 * this.sideLength * fontHeight;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.SquareEmptyContainerFillerWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.SquareEmptyContainerFillerWrapper.prototype.constructor = eqEd.SquareEmptyContainerFillerWrapper;
    eqEd.SquareEmptyContainerFillerWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper squareEmptyContainerFillerWrapper"></div>')
    }
    eqEd.SquareEmptyContainerFillerWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation);
        return copy;
    }
})();

/* End eq/js/equation-components/wrappers/squareEmptyContainerFillerWrapper.js*/

/* Begin eq/js/equation-components/wrappers/stackedFractionWrapper.js*/

eqEd.StackedFractionWrapper = function(equation) {
    eqEd.Wrapper.call(this, equation); // call super constructor.
    this.className = "eqEd.StackedFractionWrapper";

    this.stackedFractionNumeratorContainer = new eqEd.StackedFractionNumeratorContainer(this);
    this.stackedFractionDenominatorContainer = new eqEd.StackedFractionDenominatorContainer(this);
    this.stackedFractionHorizontalBar = new eqEd.StackedFractionHorizontalBar(this);
    this.domObj = this.buildDomObj();
    this.domObj.append(this.stackedFractionNumeratorContainer.domObj);
    this.domObj.append(this.stackedFractionDenominatorContainer.domObj);
    this.domObj.append(this.stackedFractionHorizontalBar.domObj);
    
    this.childNoncontainers = [this.stackedFractionHorizontalBar];
    this.childContainers = [this.stackedFractionNumeratorContainer, this.stackedFractionDenominatorContainer];

    this.padLeft = 0.05;
    this.padRight = 0.05;

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            return this.stackedFractionHorizontalBar.width;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
            return 0.5 * this.stackedFractionHorizontalBar.height + this.stackedFractionNumeratorContainer.height;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
            return 0.5 * this.stackedFractionHorizontalBar.height + this.stackedFractionDenominatorContainer.height;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.StackedFractionWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.StackedFractionWrapper.prototype.constructor = eqEd.StackedFractionWrapper;
    eqEd.StackedFractionWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper stackedFractionWrapper"></div>')
    };
    eqEd.StackedFractionWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation);
        copy.stackedFractionNumeratorContainer = this.stackedFractionNumeratorContainer.clone();
        copy.stackedFractionNumeratorContainer.parent = copy;
        copy.stackedFractionDenominatorContainer = this.stackedFractionDenominatorContainer.clone();
        copy.stackedFractionDenominatorContainer.parent = copy;
        copy.stackedFractionHorizontalBar = this.stackedFractionHorizontalBar.clone();
        copy.stackedFractionHorizontalBar.parent = copy;
        copy.domObj = copy.buildDomObj();
        copy.domObj.append(copy.stackedFractionNumeratorContainer.domObj);
        copy.domObj.append(copy.stackedFractionDenominatorContainer.domObj);
        copy.domObj.append(copy.stackedFractionHorizontalBar.domObj);

        copy.childNoncontainers = [copy.stackedFractionHorizontalBar];
        copy.childContainers = [copy.stackedFractionNumeratorContainer, copy.stackedFractionDenominatorContainer];

        return copy;
    };
    eqEd.StackedFractionWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: null,
            operands: {
                numerator: this.stackedFractionNumeratorContainer.buildJsonObj(),
                denominator: this.stackedFractionDenominatorContainer.buildJsonObj()
            }
        };
        return jsonObj;
    };
    eqEd.StackedFractionWrapper.constructFromJsonObj = function(jsonObj, equation) {
        var stackedFractionWrapper = new eqEd.StackedFractionWrapper(equation);
        for (var i = 0; i < jsonObj.operands.numerator.length; i++) {
            var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.numerator[i].type);
            var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.numerator[i], equation);
            stackedFractionWrapper.stackedFractionNumeratorContainer.addWrappers([i, innerWrapper]);
        }
        for (var i = 0; i < jsonObj.operands.denominator.length; i++) {
            var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.denominator[i].type);
            var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.denominator[i], equation);
            stackedFractionWrapper.stackedFractionDenominatorContainer.addWrappers([i, innerWrapper]);
        }
        return stackedFractionWrapper;
    }
})();

/* End eq/js/equation-components/wrappers/stackedFractionWrapper.js*/

/* Begin eq/js/equation-components/containers/stackedFractionNumeratorContainer.js*/

eqEd.StackedFractionNumeratorContainer = function(parent) {
    eqEd.Container.call(this, parent);
    this.className = "eqEd.StackedFractionNumeratorContainer";
    
    this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);

    this.padBottom = 0.025;
    this.padTop = 0.025;
    
    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var maxNumDenomWidth = (this.width > this.parent.stackedFractionDenominatorContainer.width) ? this.width : this.parent.stackedFractionDenominatorContainer.width;
            return 0.5 * (maxNumDenomWidth - this.width) + 0.5 * this.parent.stackedFractionHorizontalBar.exceedsMaxNumDenomWidth * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var fontSizeVal = "";
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            if (actualParentContainer.fontSize === "fontSizeSmaller" || actualParentContainer.fontSize === "fontSizeSmallest") {
                fontSizeVal = "fontSizeSmallest";
            } else {
                if (actualParentContainer.parent instanceof eqEd.StackedFractionWrapper) {
                    fontSizeVal = "fontSizeSmaller";
                } else {
                    fontSizeVal = "fontSizeNormal";
                }
            }
            return fontSizeVal;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.StackedFractionNumeratorContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.StackedFractionNumeratorContainer.prototype.constructor = eqEd.StackedFractionNumeratorContainer;
    eqEd.StackedFractionNumeratorContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer stackedFractionNumeratorContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/stackedFractionNumeratorContainer.js*/

/* Begin eq/js/equation-components/containers/stackedFractionDenominatorContainer.js*/

eqEd.StackedFractionDenominatorContainer = function(parent) {
    eqEd.Container.call(this, parent);
    this.className = "eqEd.StackedFractionDenominatorContainer";
    this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);
    
    this.padBottom = 0.025;
    this.padTop = 0.025;

    // Set up the padTop calculation
    var padTop = 0;
    this.properties.push(new Property(this, "padTop", padTop, {
        get: function() {
            return padTop;
        },
        set: function(value) {
            padTop = value;
        },
        compute: function() {
            var padTopVal = 0.025;
            var hasRoot = false;
            for (var i = 0; i < this.wrappers.length; i++) {
                if (this.wrappers[i] instanceof eqEd.SquareRootWrapper || this.wrappers[i] instanceof eqEd.NthRootWrapper) {
                    hasRoot = true;
                    break;
                }
            }
            if (hasRoot) {
                padTopVal = 0.1;
            }
            return padTopVal;
        },
        updateDom: function() {}
    }));


    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var maxNumDenomWidth = (this.width > this.parent.stackedFractionNumeratorContainer.width) ? this.width : this.parent.stackedFractionNumeratorContainer.width;
            return 0.5 * (maxNumDenomWidth - this.width) + 0.5 * this.parent.stackedFractionHorizontalBar.exceedsMaxNumDenomWidth * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            return this.parent.stackedFractionNumeratorContainer.height + this.parent.stackedFractionHorizontalBar.height;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var fontSizeVal = "";
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            if (actualParentContainer.fontSize === "fontSizeSmaller" || actualParentContainer.fontSize === "fontSizeSmallest") {
                fontSizeVal = "fontSizeSmallest";
            } else {
                if (actualParentContainer.parent instanceof eqEd.StackedFractionWrapper) {
                    fontSizeVal = "fontSizeSmaller";
                } else {
                    fontSizeVal = "fontSizeNormal";
                }
            }
            return fontSizeVal;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.StackedFractionDenominatorContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.StackedFractionDenominatorContainer.prototype.constructor = eqEd.StackedFractionDenominatorContainer;
    eqEd.StackedFractionDenominatorContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer stackedFractionDenominatorContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/stackedFractionDenominatorContainer.js*/

/* Begin eq/js/equation-components/misc/stackedFractionHorizontalBar.js*/

eqEd.StackedFractionHorizontalBar = function(parent) {
    eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
    this.className = "eqEd.StackedFractionHorizontalBar";

    this.domObj = this.buildDomObj();
    this.exceedsMaxNumDenomWidth = 0.25;
    this.barHeightRatio = 0.05;

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var maxNumDenomWidth = (this.parent.stackedFractionDenominatorContainer.width > this.parent.stackedFractionNumeratorContainer.width) ? this.parent.stackedFractionDenominatorContainer.width : this.parent.stackedFractionNumeratorContainer.width;
            return maxNumDenomWidth + this.exceedsMaxNumDenomWidth * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height["fontSizeNormal"];
            return this.barHeightRatio * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            return this.parent.stackedFractionNumeratorContainer.height;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.StackedFractionHorizontalBar.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.StackedFractionHorizontalBar.prototype.constructor = eqEd.StackedFractionHorizontalBar;
    eqEd.StackedFractionHorizontalBar.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="stackedFractionHorizontalBar"></div>');
    };
})();

/* End eq/js/equation-components/misc/stackedFractionHorizontalBar.js*/

/* Begin eq/js/equation-components/wrappers/superscriptWrapper.js*/

eqEd.SuperscriptWrapper = function(equation) {
	eqEd.Wrapper.call(this, equation); // call super constructor.
    this.className = "eqEd.SuperscriptWrapper";

    this.superscriptContainer = new eqEd.SuperscriptContainer(this);
    this.domObj = this.buildDomObj();
    this.domObj.append(this.superscriptContainer.domObj);
    this.childContainers = [this.superscriptContainer];
    this.maxBaseWrapperOverlap = 0.9;

    // Set up the padRight calculation
    var padRight = 0;
    this.properties.push(new Property(this, "padRight", padRight, {
        get: function() {
            return padRight;
        },
        set: function(value) {
            padRight = value;
        },
        compute: function() {
            var padRightVal = 0.05;
            if (this.index !== 0 
                && this.parent.wrappers[this.index - 1] instanceof eqEd.FunctionWrapper) {
                if (this.parent.wrappers[this.index + 1] instanceof eqEd.BracketWrapper
                    || this.parent.wrappers[this.index + 1] instanceof eqEd.BracketPairWrapper) {
                    padRightVal = 0.05;
                } else {
                    padRightVal = 0.175;
                }
            }
            return padRightVal;
        },
        updateDom: function() {}
    }));

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            return this.superscriptContainer.width;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
        	var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
        	var baseWrapper = null;
        	var baseWrapperOverlap = 0.75;

        	if (this.index !== 0) {
        		baseWrapper = this.parent.wrappers[this.index - 1];
        	} else {
        		// The superscript wrapper is the first entry in the container.
        		// We want to format it, as if there is a symbol immediately
        		// preceeding it.
        		baseWrapper = new eqEd.SymbolWrapper(this.equation, 'a', 'MathJax_MathItalic');
                baseWrapper.parent = this.parent;
        		baseWrapper.index = 0;
                // Can't just call baseWrapper.update(), because it creates a circular reference
                for (var i = 0; i < baseWrapper.properties.length; i++) {
                    var prop = baseWrapper.properties[i];
                    if (prop.propName !== "top" && prop.propName !== "left") {
                        prop.compute();
                    }
                }
        	}
        	var topAlign = 0;
            var superscriptContainerTopAlign = 0;
            var superscriptContainerBottomAlign = 0;
            if (this.superscriptContainer.wrappers.length !== 0) {
                superscriptContainerTopAlign = this.superscriptContainer.wrappers[this.superscriptContainer.maxTopAlignIndex].topAlign;
                superscriptContainerBottomAlign = this.superscriptContainer.wrappers[this.superscriptContainer.maxBottomAlignIndex].bottomAlign;
            }
            // topAlign rules if previous wrapper is an nth root wrapper.
        	if (baseWrapper instanceof eqEd.NthRootWrapper) {
                if (baseWrapper.nthRootDegreeContainer.isLeftFlushToWrapper) {
                    topAlign = baseWrapper.nthRootDiagonal.height + superscriptContainerTopAlign - baseWrapper.bottomAlign;
                } else {
                    topAlign = baseWrapper.topAlign + superscriptContainerTopAlign;
                }
            // topAlign rules if previous wrapper is a square root wrapper
            } else if (baseWrapper instanceof eqEd.SquareRootWrapper) {
	            topAlign = baseWrapper.topAlign + superscriptContainerTopAlign;
            // topAlign rules if previous wrapper has a superscript as well.
	        } else if (baseWrapper instanceof eqEd.SuperscriptWrapper || baseWrapper instanceof eqEd.SuperscriptAndSubscriptWrapper) {
                var base = baseWrapper.superscriptContainer;
                var baseTopAlign = 0;
                if (base.wrappers.length !== 0) {
                    baseTopAlign = base.wrappers[base.maxTopAlignIndex].topAlign;
                }
                var offset = 0.15;
                var maxOverlap = 0.75;
                // Check if the superscript container overlaps with more than the maxOverlap ration of the previous superscript container.
                if (superscriptContainerBottomAlign + offset * fontHeight > maxOverlap * base.height) {
                    topAlign = baseWrapper.topAlign + (this.superscriptContainer.height - maxOverlap * base.height);
                } else {
                    topAlign = baseWrapper.topAlign + superscriptContainerTopAlign - offset * fontHeight;
                }
            } else {
                var offset = 0.3;
                var maxOverlap = 0.75;
                // Check if the superscript container overlaps with more than the maxOverlap ration of the baseWrapper
                if (superscriptContainerBottomAlign + offset * fontHeight > maxOverlap * baseWrapper.height) {
                    topAlign = baseWrapper.topAlign + (this.superscriptContainer.height - maxOverlap * baseWrapper.height);
                } else {
                    topAlign = baseWrapper.topAlign + superscriptContainerTopAlign - offset * fontHeight;
                }
            }
            return topAlign;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
        	var baseWrapper = null;
        	if (this.index !== 0) {
        		baseWrapper = this.parent.wrappers[this.index - 1];
        	} else {
        		// The superscript wrapper is the first entry in the container.
        		// We want to format it, as if there is a symbol immediately
        		// preceeding it.
        		baseWrapper = new eqEd.SymbolWrapper(this.equation, 'a', 'MathJax_MathItalic');
        		baseWrapper.index = 0;
                baseWrapper.parent = this.parent;
        		// Can't just call baseWrapper.update(), because it creates a circular reference
                for (var i = 0; i < baseWrapper.properties.length; i++) {
                    var prop = baseWrapper.properties[i];
                    if (prop.propName !== "top" && prop.propName !== "left") {
                        prop.compute();
                    }
                }
        	}
            return baseWrapper.bottomAlign;
        },
        updateDom: function() {}
    }));
};

(function() {
    // subclass extends superclass
    eqEd.SuperscriptWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.SuperscriptWrapper.prototype.constructor = eqEd.SuperscriptWrapper;
    eqEd.SuperscriptWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper superscriptWrapper"></div>')
    };
    eqEd.SuperscriptWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation);
        copy.superscriptContainer = this.superscriptContainer.clone();
        copy.superscriptContainer.parent = copy;
    	copy.domObj = copy.buildDomObj();
    	copy.domObj.append(copy.superscriptContainer.domObj);
    	copy.childContainers = [copy.superscriptContainer];
        return copy;
    };
    eqEd.SuperscriptWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: null,
            operands: {
                superscript: this.superscriptContainer.buildJsonObj()
            }
        };
        return jsonObj;
    };
    eqEd.SuperscriptWrapper.constructFromJsonObj = function(jsonObj, equation) {
        var superscriptWrapper = new eqEd.SuperscriptWrapper(equation);
        for (var i = 0; i < jsonObj.operands.superscript.length; i++) {
            var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.superscript[i].type);
            var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.superscript[i], equation);
            superscriptWrapper.superscriptContainer.addWrappers([i, innerWrapper]);
        }
        return superscriptWrapper;
    }
})();

/* End eq/js/equation-components/wrappers/superscriptWrapper.js*/

/* Begin eq/js/equation-components/containers/superscriptContainer.js*/

eqEd.SuperscriptContainer = function(parent) {
	eqEd.Container.call(this, parent);
	this.className = "eqEd.SuperscriptContainer";

	this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
        	// remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
        	// remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
        	var fontSizeVal = "";
           	var baseWrapper = null;
	        if (this.parent.index !== 0) {
	            baseWrapper = this.parent.parent.wrappers[this.parent.index - 1];
	        } else {
	            // The superscript wrapper is the first entry in the container.
        		// We want to format it, as if there is a symbol immediately
        		// preceeding it.
        		baseWrapper = null;
	        }

            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            
	        if (actualParentContainer.fontSize === "fontSizeSmaller" || actualParentContainer.fontSize === "fontSizeSmallest") {
	            fontSizeVal = "fontSizeSmallest";
	        } else {
	            if (baseWrapper instanceof eqEd.SuperscriptWrapper
	             || baseWrapper instanceof eqEd.SuperscriptAndSubscriptWrapper) {
	                fontSizeVal = "fontSizeSmallest";
	            } else {
	                fontSizeVal = "fontSizeSmaller";
	            }
	        }
	        return fontSizeVal;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};

(function() {
    // subclass extends superclass
    eqEd.SuperscriptContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.SuperscriptContainer.prototype.constructor = eqEd.SuperscriptContainer;
    eqEd.SuperscriptContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer superscriptContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/superscriptContainer.js*/

/* Begin eq/js/equation-components/wrappers/subscriptWrapper.js*/

eqEd.SubscriptWrapper = function(equation) {
	eqEd.Wrapper.call(this, equation); // call super constructor.
    this.className = "eqEd.SubscriptWrapper";

    this.subscriptContainer = new eqEd.SubscriptContainer(this);
    this.domObj = this.buildDomObj();
    this.domObj.append(this.subscriptContainer.domObj);
    this.childContainers = [this.subscriptContainer];

    // Set up the padRight calculation
    var padRight = 0;
    this.properties.push(new Property(this, "padRight", padRight, {
        get: function() {
            return padRight;
        },
        set: function(value) {
            padRight = value;
        },
        compute: function() {
            var padRightVal = 0.05;
            if (this.index !== 0 
                && this.parent.wrappers[this.index - 1] instanceof eqEd.FunctionWrapper) {
                if (this.parent.wrappers[this.index + 1] instanceof eqEd.BracketWrapper
                    || this.parent.wrappers[this.index + 1] instanceof eqEd.BracketPairWrapper) {
                    padRightVal = 0.05;
                } else {
                    padRightVal = 0.175;
                }
            }
            return padRightVal;
        },
        updateDom: function() {}
    }));

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            return this.subscriptContainer.width;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
        	var baseWrapper = null;
        	if (this.index !== 0) {
        		baseWrapper = this.parent.wrappers[this.index - 1];
        	} else {
        		// The subscript wrapper is the first entry in the container.
        		// We want to format it, as if there is a symbol immediately
        		// preceeding it.
        		baseWrapper = new eqEd.SymbolWrapper(this.equation, 'a', 'MathJax_MathItalic');
        		baseWrapper.index = 0;
                baseWrapper.parent = this.parent;
        		// Can't just call baseWrapper.update(), because it creates a circular reference
                for (var i = 0; i < baseWrapper.properties.length; i++) {
                    var prop = baseWrapper.properties[i];
                    if (prop.propName !== "top" && prop.propName !== "left") {
                        prop.compute();
                    }
                }
        	}
            return baseWrapper.topAlign;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
        	var baseWrapper = null;
            var base = null;
        	if (this.index !== 0) {
        		baseWrapper = this.parent.wrappers[this.index - 1];
                if (baseWrapper instanceof eqEd.SubscriptWrapper) {
                    base = baseWrapper.subscriptContainer;
                } else {
                    base = baseWrapper;
                }
        	} else {
        		// The subscript wrapper is the first entry in the container.
        		// We want to format it, as if there is a symbol immediately
        		// preceeding it.
        		baseWrapper = new eqEd.SymbolWrapper(this.equation, 'a', 'MathJax_MathItalic');
        		baseWrapper.index = 0;
                baseWrapper.parent = this.parent;
        		// Can't just call baseWrapper.update(), because it creates a circular reference
                for (var i = 0; i < baseWrapper.properties.length; i++) {
                    var prop = baseWrapper.properties[i];
                    if (prop.propName !== "top" && prop.propName !== "left") {
                        prop.compute();
                    }
                }
        	}
            var fontHeightNested = this.equation.fontMetrics.height[this.subscriptContainer.fontSize];
            return this.subscriptContainer.height + baseWrapper.bottomAlign - this.subscriptContainer.offsetTop * fontHeightNested;
        },
        updateDom: function() {}
    }));
};

(function() {
    // subclass extends superclass
    eqEd.SubscriptWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.SubscriptWrapper.prototype.constructor = eqEd.SubscriptWrapper;
    eqEd.SubscriptWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper subscriptWrapper"></div>')
    };
    eqEd.SubscriptWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation);
        copy.subscriptContainer = this.subscriptContainer.clone();
        copy.subscriptContainer.parent = copy;
        copy.domObj = copy.buildDomObj();
        copy.domObj.append(copy.subscriptContainer.domObj);
        copy.childContainers = [copy.subscriptContainer];

        return copy;
    };
    eqEd.SubscriptWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: null,
            operands: {
                subscript: this.subscriptContainer.buildJsonObj()
            }
        };
        return jsonObj;
    };
    eqEd.SubscriptWrapper.constructFromJsonObj = function(jsonObj, equation) {
        var subscriptWrapper = new eqEd.SubscriptWrapper(equation);
        for (var i = 0; i < jsonObj.operands.subscript.length; i++) {
            var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.subscript[i].type);
            var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.subscript[i], equation);
            subscriptWrapper.subscriptContainer.addWrappers([i, innerWrapper]);
        }
        return subscriptWrapper;
    }
})();

/* End eq/js/equation-components/wrappers/subscriptWrapper.js*/

/* Begin eq/js/equation-components/containers/subscriptContainer.js*/

eqEd.SubscriptContainer = function(parent) {
	eqEd.Container.call(this, parent);
	this.className = "eqEd.SubscriptContainer";

	this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);
    this.offsetTop = 0.75;

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
        	// remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
        	var baseWrapper = null;
            if (this.parent.index !== 0) {
                baseWrapper = this.parent.parent.wrappers[this.parent.index - 1];
            } else {
                baseWrapper = new eqEd.SymbolWrapper(this.equation, 'a', 'MathJax_MathItalic');
                baseWrapper.parent = this.parent.parent;
                baseWrapper.index = 0;
                baseWrapper.update();
            }
            var fontHeight = this.equation.fontMetrics.height[this.fontSize];
            return this.parent.topAlign + baseWrapper.bottomAlign - this.offsetTop * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
        	var fontSizeVal = "";
           	var baseWrapper = null;
	        if (this.parent.index !== 0) {
	            baseWrapper = this.parent.parent.wrappers[this.parent.index - 1];
	        } else {
	            // The superscript wrapper is the first entry in the container.
        		// We want to format it, as if there is a symbol immediately
        		// preceeding it.
        		baseWrapper = null;
	        }

            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            
	        if (actualParentContainer.fontSize === "fontSizeSmaller" || actualParentContainer.fontSize === "fontSizeSmallest") {
	            fontSizeVal = "fontSizeSmallest";
	        } else {
	            if (baseWrapper instanceof eqEd.SubscriptWrapper
	             || baseWrapper instanceof eqEd.SuperscriptAndSubscriptWrapper) {
	                fontSizeVal = "fontSizeSmallest";
	            } else {
	                fontSizeVal = "fontSizeSmaller";
	            }
	        }
	        return fontSizeVal;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};

(function() {
    // subclass extends superclass
    eqEd.SubscriptContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.SubscriptContainer.prototype.constructor = eqEd.SubscriptContainer;
    eqEd.SubscriptContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer subscriptContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/subscriptContainer.js*/

/* Begin eq/js/equation-components/wrappers/superscriptAndSubscriptWrapper.js*/

eqEd.SuperscriptAndSubscriptWrapper = function(equation) {
	eqEd.Wrapper.call(this, equation); // call super constructor.
    this.className = "eqEd.SuperscriptAndSubscriptWrapper";

    this.superscriptContainer = new eqEd.SuperscriptContainer(this);
    this.subscriptContainer = new eqEd.SubscriptContainer(this);
    this.subscriptContainer.offsetTop = 0.45;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.superscriptContainer.domObj);
    this.domObj.append(this.subscriptContainer.domObj);
    this.childContainers = [this.superscriptContainer, this.subscriptContainer];

    // Set up the padRight calculation
    var padRight = 0;
    this.properties.push(new Property(this, "padRight", padRight, {
        get: function() {
            return padRight;
        },
        set: function(value) {
            padRight = value;
        },
        compute: function() {
            var padRightVal = 0.05;
            if (this.index !== 0 
                && this.parent.wrappers[this.index - 1] instanceof eqEd.FunctionWrapper) {
                if (this.parent.wrappers[this.index + 1] instanceof eqEd.BracketWrapper
                    || this.parent.wrappers[this.index + 1] instanceof eqEd.BracketPairWrapper) {
                    padRightVal = 0.05;
                } else {
                    padRightVal = 0.175;
                }
            }
            return padRightVal;
        },
        updateDom: function() {}
    }));

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
        	var maxWidth = (this.superscriptContainer.width > this.subscriptContainer.width) ? this.superscriptContainer.width : this.subscriptContainer.width;
            return maxWidth;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
        	var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            var baseWrapper = null;
            var baseWrapperOverlap = 0.75;

            if (this.index !== 0) {
                baseWrapper = this.parent.wrappers[this.index - 1];
            } else {
                // The superscript wrapper is the first entry in the container.
                // We want to format it, as if there is a symbol immediately
                // preceeding it.
                baseWrapper = new eqEd.SymbolWrapper(this.equation, 'a', 'MathJax_MathItalic');
                baseWrapper.parent = this.parent;
                baseWrapper.index = 0;
                // Can't just call baseWrapper.update(), because it creates a circular reference
                for (var i = 0; i < baseWrapper.properties.length; i++) {
                    var prop = baseWrapper.properties[i];
                    if (prop.propName !== "top" && prop.propName !== "left") {
                        prop.compute();
                    }
                }
            }
            var topAlign = 0;
            var superscriptContainerTopAlign = 0;
            var superscriptContainerBottomAlign = 0;
            if (this.superscriptContainer.wrappers.length !== 0) {
                superscriptContainerTopAlign = this.superscriptContainer.wrappers[this.superscriptContainer.maxTopAlignIndex].topAlign;
                superscriptContainerBottomAlign = this.superscriptContainer.wrappers[this.superscriptContainer.maxBottomAlignIndex].bottomAlign;
            }
            // topAlign rules if previous wrapper is an nth root wrapper.
            if (baseWrapper instanceof eqEd.NthRootWrapper) {
                if (baseWrapper.nthRootDegreeContainer.isLeftFlushToWrapper) {
                    topAlign = baseWrapper.nthRootDiagonal.height + superscriptContainerTopAlign - baseWrapper.bottomAlign;
                } else {
                    topAlign = baseWrapper.topAlign + superscriptContainerTopAlign;
                }
            // topAlign rules if previous wrapper is a square root wrapper
            } else if (baseWrapper instanceof eqEd.SquareRootWrapper) {
                topAlign = baseWrapper.topAlign + superscriptContainerTopAlign;
            // topAlign rules if previous wrapper has a superscript as well.
            } else if (baseWrapper instanceof eqEd.SuperscriptWrapper || baseWrapper instanceof eqEd.SuperscriptAndSubscriptWrapper) {
                var base = baseWrapper.superscriptContainer;
                var baseTopAlign = 0;
                if (base.wrappers.length !== 0) {
                    baseTopAlign = base.wrappers[base.maxTopAlignIndex].topAlign;
                }
                var offset = 0.15;
                var maxOverlap = 0.75;
                // Check if the superscript container overlaps with more than the maxOverlap ration of the previous superscript container.
                if (superscriptContainerBottomAlign + offset * fontHeight > maxOverlap * base.height) {
                    topAlign = baseWrapper.topAlign + (this.superscriptContainer.height - maxOverlap * base.height);
                } else {
                    topAlign = baseWrapper.topAlign + superscriptContainerTopAlign - offset * fontHeight;
                }
            } else {
                var offset = 0.3;
                var maxOverlap = 0.625;
                // Check if the superscript container overlaps with more than the maxOverlap ration of the baseWrapper
                if (superscriptContainerBottomAlign + offset * fontHeight > maxOverlap * baseWrapper.height) {
                    topAlign = baseWrapper.topAlign + (this.superscriptContainer.height - maxOverlap * baseWrapper.height);
                } else {
                    topAlign = baseWrapper.topAlign + superscriptContainerTopAlign - offset * fontHeight;
                }
            }
            return topAlign;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
        	var baseWrapper = null;
            var base = null;
        	if (this.index !== 0) {
        		baseWrapper = this.parent.wrappers[this.index - 1];
                if (baseWrapper instanceof eqEd.SubscriptWrapper) {
                    base = baseWrapper.subscriptContainer;
                } else {
                    base = baseWrapper;
                }
        	} else {
        		// The subscript wrapper is the first entry in the container.
        		// We want to format it, as if there is a symbol immediately
        		// preceeding it.
        		baseWrapper = new eqEd.SymbolWrapper(this.equation, 'a', 'MathJax_MathItalic');
        		baseWrapper.index = 0;
                baseWrapper.parent = this.parent;
        		// Can't just call baseWrapper.update(), because it creates a circular reference
                for (var i = 0; i < baseWrapper.properties.length; i++) {
                    var prop = baseWrapper.properties[i];
                    if (prop.propName !== "top" && prop.propName !== "left") {
                        prop.compute();
                    }
                }
                base = baseWrapper;
        	}
            var fontHeightNested = this.equation.fontMetrics.height[this.subscriptContainer.fontSize];
            return this.subscriptContainer.height + baseWrapper.bottomAlign - this.subscriptContainer.offsetTop * fontHeightNested;
        },
        updateDom: function() {}
    }));
};

(function() {
    // subclass extends superclass
    eqEd.SuperscriptAndSubscriptWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.SuperscriptAndSubscriptWrapper.prototype.constructor = eqEd.SuperscriptAndSubscriptWrapper;
    eqEd.SuperscriptAndSubscriptWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper superscriptAndSubscriptWrapper"></div>')
    };
    eqEd.SuperscriptAndSubscriptWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation);

        copy.superscriptContainer = this.superscriptContainer.clone();
        copy.superscriptContainer.parent = copy;
	    copy.subscriptContainer = this.subscriptContainer.clone();
        copy.subscriptContainer.parent = copy;
	    copy.subscriptContainer.offsetTop = 0.45;
	    copy.domObj = copy.buildDomObj();
	    copy.domObj.append(copy.superscriptContainer.domObj);
	    copy.domObj.append(copy.subscriptContainer.domObj);
	    copy.childContainers = [copy.superscriptContainer, copy.subscriptContainer];
        return copy;
    };
    eqEd.SuperscriptAndSubscriptWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: null,
            operands: {
                superscript: this.superscriptContainer.buildJsonObj(),
                subscript: this.subscriptContainer.buildJsonObj()
            }
        };
        return jsonObj;
    };
    eqEd.SuperscriptAndSubscriptWrapper.constructFromJsonObj = function(jsonObj, equation) {
        var superscriptAndSubscriptWrapper = new eqEd.SuperscriptAndSubscriptWrapper(equation);
        for (var i = 0; i < jsonObj.operands.superscript.length; i++) {
            var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.superscript[i].type);
            var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.superscript[i], equation);
            superscriptAndSubscriptWrapper.superscriptContainer.addWrappers([i, innerWrapper]);
        }
        for (var i = 0; i < jsonObj.operands.subscript.length; i++) {
            var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.subscript[i].type);
            var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.subscript[i], equation);
            superscriptAndSubscriptWrapper.subscriptContainer.addWrappers([i, innerWrapper]);
        }
        return superscriptAndSubscriptWrapper;
    }
})();

/* End eq/js/equation-components/wrappers/superscriptAndSubscriptWrapper.js*/

/* Begin eq/js/equation-components/wrappers/squareRootWrapper.js*/

eqEd.SquareRootWrapper = function(equation) {
	eqEd.Wrapper.call(this, equation); // call super constructor.
    this.className = "eqEd.SquareRootWrapper";

    this.domObj = this.buildDomObj();    

    this.radicandContainer = new eqEd.SquareRootRadicandContainer(this);
    this.squareRootOverBar = new eqEd.SquareRootOverBar(this);
    this.radical = new eqEd.SquareRootRadical(this);
    this.squareRootDiagonal = new eqEd.SquareRootDiagonal(this);
    this.domObj.append(this.radicandContainer.domObj);
    this.domObj.append(this.squareRootOverBar.domObj);
    this.domObj.append(this.radical.domObj);
    this.domObj.append(this.squareRootDiagonal.domObj);
    this.childContainers = [this.radicandContainer];
    this.childNoncontainers = [this.squareRootDiagonal, this.radical, this.squareRootOverBar];

    this.padLeft = 0.1;
    this.padRight = 0.1;

    // Set up the padBottom calculation
    var padBottom = 0;
    this.properties.push(new Property(this, "padBottom", padBottom, {
        get: function() {
            return padBottom;
        },
        set: function(value) {
            padBottom = value;
        },
        compute: function() {
            var padBottomVal = 0;
            if (this.parent instanceof eqEd.StackedFractionNumeratorContainer) {
                padBottomVal = 0.2;
            }
            return padBottomVal;
        },
        updateDom: function() {}
    }));

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            return this.radical.width + this.squareRootDiagonal.width + this.radicandContainer.width;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
        	var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            var topAlignVal = 0;
            if (this.radicandContainer.wrappers.length > 0) {
                topAlignVal += this.radicandContainer.wrappers[this.radicandContainer.maxTopAlignIndex].topAlign;
            }
            if (this.radicandContainer.isMaxTopAlignRootWrapper) {
                topAlignVal += this.radicandContainer.padTopMaxChildAlignTopIsRoot * fontHeight;
            } else {
                topAlignVal += this.radicandContainer.padTopMaxChildAlignTopIsNotRoot * fontHeight;
            }
            
            return topAlignVal;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
        	var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            var bottomAlignVal = 0;
            if (this.radicandContainer.wrappers.length > 0) {
                bottomAlignVal += this.radicandContainer.wrappers[this.radicandContainer.maxBottomAlignIndex].bottomAlign;
            }
            if (this.radicandContainer.isMaxTopAlignRootWrapper) {
                bottomAlignVal += this.radicandContainer.padBottomMaxChildAlignTopIsRoot * fontHeight;
            } else {
                bottomAlignVal += this.radicandContainer.padBottomMaxChildAlignTopIsNotRoot * fontHeight;
            }
            return bottomAlignVal;
        },
        updateDom: function() {}
    }));
};

(function() {
    // subclass extends superclass
    eqEd.SquareRootWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.SquareRootWrapper.prototype.constructor = eqEd.SquareRootWrapper;
    eqEd.SquareRootWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper squareRootWrapper"></div>')
    };
    eqEd.SquareRootWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation);

        copy.domObj = copy.buildDomObj();    

        copy.radicandContainer = this.radicandContainer.clone();
        copy.radicandContainer.parent = copy;
        copy.squareRootOverBar = this.squareRootOverBar.clone();
        copy.squareRootOverBar.parent = copy;
        copy.radical = this.radical.clone();
        copy.radical.parent = copy;
        copy.squareRootDiagonal = this.squareRootDiagonal.clone();
        copy.squareRootDiagonal.parent = copy;
        copy.domObj.append(copy.radicandContainer.domObj);
        copy.domObj.append(copy.squareRootOverBar.domObj);
        copy.domObj.append(copy.radical.domObj);
        copy.domObj.append(copy.squareRootDiagonal.domObj);
        copy.childContainers = [copy.radicandContainer];
        copy.childNoncontainers = [copy.squareRootDiagonal, copy.radical, copy.squareRootOverBar];

        return copy;
    };
    eqEd.SquareRootWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: null,
            operands: {
                radicand: this.radicandContainer.buildJsonObj()
            }
        };
        return jsonObj;
    };
    eqEd.SquareRootWrapper.constructFromJsonObj = function(jsonObj, equation) {
        var squareRootWrapper = new eqEd.SquareRootWrapper(equation);
        for (var i = 0; i < jsonObj.operands.radicand.length; i++) {
            var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.radicand[i].type);
            var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.radicand[i], equation);
            squareRootWrapper.radicandContainer.addWrappers([i, innerWrapper]);
        }
        return squareRootWrapper;
    }
})();

/* End eq/js/equation-components/wrappers/squareRootWrapper.js*/

/* Begin eq/js/equation-components/misc/squareRootOverBar.js*/

eqEd.SquareRootOverBar = function(parent) {
    eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
    this.className = "eqEd.SquareRootOverBar";

    this.domObj = this.buildDomObj();
    this.adjustLeft = -0.06;
    this.heightRatio = 0.055;

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return this.parent.radicandContainer.width - this.adjustLeft * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height["fontSizeNormal"];
            return this.heightRatio * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            return this.parent.radical.width + this.parent.squareRootDiagonal.width;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.SquareRootOverBar.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.SquareRootOverBar.prototype.constructor = eqEd.SquareRootOverBar;
    eqEd.SquareRootOverBar.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="squareRootOverBar"></div>');
    };
})();

/* End eq/js/equation-components/misc/squareRootOverBar.js*/

/* Begin eq/js/equation-components/misc/squareRootRadical.js*/

eqEd.SquareRootRadical = function(parent) {
    eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
    this.className = "eqEd.SquareRootRadical";

    this.domObj = this.buildDomObj();

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var widthVal = 0;
            if (this.parent.squareRootDiagonal.height < 2 * fontHeight) {
                widthVal = 0.4 * fontHeight;
            } else {
                widthVal = 0.5 * fontHeight;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var heightVal = 0;
            if (this.parent.squareRootDiagonal.height < 2 * fontHeight) {
                heightVal = 0.7 * fontHeight;
            } else {
                heightVal = 0.75 * fontHeight;
            }
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            return this.parent.squareRootDiagonal.height - this.height;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.SquareRootRadical.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.SquareRootRadical.prototype.constructor = eqEd.SquareRootRadical;
    eqEd.SquareRootRadical.prototype.buildDomObj = function() {
        var htmlRep = '<div class="squareRootRadical" style="width: 74.842293px; height: 127.48769px;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 74.842293 127.48769" preserveAspectRatio="none"><defs id="defs4"><clipPath clipPathUnits="userSpaceOnUse" id="clipPath3765"><rect style="fill:#b1ded2;fill-opacity:1;stroke:none" id="rect3767" width="74.842293" height="127.62585" x="198.84776" y="668.99451" /></clipPath></defs><g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1" transform="translate(-198.84776,-668.99451)"><g clip-path="url(#clipPath3765)"><path d="m 265.30006,796.48219 -47.75994,-111.23309 -14.88621,11.47479 -3.82493,-3.82493 30.28931,-23.46646 44.65864,103.89336 109.8892,-228.9789 c 0.68896,-1.30943 1.89502,-1.96414 3.61817,-1.96415 1.17139,1e-5 2.17069,0.41351 2.99792,1.24052 0.8268,0.82701 1.2403,1.82632 1.24052,2.99791 -2.2e-4,0.68918 -0.0691,1.17161 -0.20676,1.44728 L 273.15667,794.51804 c -0.55144,1.30919 -1.61966,1.96391 -3.20467,1.96415 l -4.65194,0" style="" id="path2987" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/squareRootRadical.js*/

/* Begin eq/js/equation-components/misc/squareRootDiagonal.js*/

eqEd.SquareRootDiagonal = function(parent) {
    eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
    this.className = "eqEd.SquareRootDiagonal";

    this.domObj = this.buildDomObj();
    this.adjustLeft = -0.035;

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 0.58 * fontHeight + 0.05 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var heightVal = this.parent.radicandContainer.height;
            if (this.parent.radicandContainer.isMaxTopAlignRootWrapper) {
                heightVal += (this.parent.radicandContainer.padTopMaxChildAlignTopIsRoot + this.parent.radicandContainer.padBottomMaxChildAlignTopIsRoot) * fontHeight;
            } else {
                heightVal += (this.parent.radicandContainer.padTopMaxChildAlignTopIsNotRoot + this.parent.radicandContainer.padBottomMaxChildAlignTopIsNotRoot) * fontHeight;
            }
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            return this.parent.radical.width;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.SquareRootDiagonal.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.SquareRootDiagonal.prototype.constructor = eqEd.SquareRootDiagonal;
    eqEd.SquareRootDiagonal.prototype.buildDomObj = function() {
        var htmlRep = '<div class="squareRootDiagonal" style="width: 130.0331px; height: 256.45282px;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 130.0331 256.45282" preserveAspectRatio="none"><g transform="translate(-391.39675,-547.35338)"><g transform="scale(1.1433177,0.87464752)" style="font-size:162.99891663px;font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;line-height:125%;letter-spacing:0px;word-spacing:0px;fill:#000000;fill-opacity:1;stroke:none;font-family:cmex10;-inkscape-font-specification:cmex10" id="text2989"><path d="m 342.3342,915.58351 0,-0.95508 c 0.053,-0.1064 0.0796,-0.21252 0.0796,-0.31835 0.053,-0.1064 0.0796,-0.21252 0.0796,-0.31836 L 448.42675,628.42526 c 0.42437,-1.37954 1.51208,-2.25503 3.26317,-2.62645 l 0.95507,0 c 1.75084,0.31836 2.89162,1.45914 3.42234,3.42234 l 0,0.95507 c -1.2e-4,0.10612 -0.0267,0.23877 -0.0796,0.39795 -1.2e-4,0.10612 -0.0266,0.21224 -0.0796,0.31836 L 349.97477,916.45899 c -0.42449,1.3262 -1.51221,2.17515 -3.26316,2.54686 l -0.95507,0 c -1.75098,-0.31865 -2.89176,-1.45943 -3.42234,-3.42234" style="" id="path2987" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/squareRootDiagonal.js*/

/* Begin eq/js/equation-components/containers/squareRootRadicandContainer.js*/

eqEd.SquareRootRadicandContainer = function(parent) {
	eqEd.Container.call(this, parent);
	this.className = "eqEd.SquareRootRadicandContainer";

	this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);
    this.padTopMaxChildAlignTopIsRoot = 0.45;
    this.padTopMaxChildAlignTopIsNotRoot = 0.15;
    this.padBottomMaxChildAlignTopIsRoot = 0.2;
    this.padBottomMaxChildAlignTopIsNotRoot = 0;
    
    // Set up the isMaxTopAlignRootWrapper calculation
    var isMaxTopAlignRootWrapper = false;
    this.properties.push(new Property(this, "isMaxTopAlignRootWrapper", isMaxTopAlignRootWrapper, {
        get: function() {
            return isMaxTopAlignRootWrapper;
        },
        set: function(value) {
            isMaxTopAlignRootWrapper = value;
        },
        compute: function() {
            var maxTopAlignIndexWrapper = this.wrappers[this.maxTopAlignIndex];
            return (maxTopAlignIndexWrapper instanceof eqEd.SquareRootWrapper || maxTopAlignIndexWrapper instanceof eqEd.NthRootWrapper);
        },
        updateDom: function() {}
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            return this.parent.radical.width + this.parent.squareRootDiagonal.width;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
        	var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var topVal = 0;
            if (this.isMaxTopAlignRootWrapper) {
                topVal += this.padTopMaxChildAlignTopIsRoot * fontHeight;
            } else {
                topVal += this.padTopMaxChildAlignTopIsNotRoot * fontHeight;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
	        return actualParentContainer.fontSize;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};

(function() {
    // subclass extends superclass
    eqEd.SquareRootRadicandContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.SquareRootRadicandContainer.prototype.constructor = eqEd.SquareRootRadicandContainer;
    eqEd.SquareRootRadicandContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer squareRootRadicandContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/squareRootRadicandContainer.js*/

/* Begin eq/js/equation-components/wrappers/nthRootWrapper.js*/

eqEd.NthRootWrapper = function(equation) {
	eqEd.Wrapper.call(this, equation); // call super constructor.
    this.className = "eqEd.NthRootWrapper";

    this.domObj = this.buildDomObj();    

    this.radicandContainer = new eqEd.NthRootRadicandContainer(this);
    this.nthRootOverBar = new eqEd.NthRootOverBar(this);
    this.radical = new eqEd.NthRootRadical(this);
    this.nthRootDiagonal = new eqEd.NthRootDiagonal(this);
    this.nthRootDegreeContainer = new eqEd.NthRootDegreeContainer(this);
    this.domObj.append(this.nthRootDegreeContainer.domObj);
    this.domObj.append(this.radicandContainer.domObj);
    this.domObj.append(this.nthRootOverBar.domObj);
    this.domObj.append(this.radical.domObj);
    this.domObj.append(this.nthRootDiagonal.domObj);
    this.childContainers = [this.nthRootDegreeContainer, this.radicandContainer];
    this.childNoncontainers = [this.nthRootDiagonal, this.radical, this.nthRootOverBar];

    this.padBottomWhenParentIsFraction = 0.2;
    this.padLeft = 0.1;
    this.padRight = 0.1;
    //this.padTop = 0.1;

    // Set up the padBottom calculation
    var padBottom = 0;
    this.properties.push(new Property(this, "padBottom", padBottom, {
        get: function() {
            return padBottom;
        },
        set: function(value) {
            padBottom = value;
        },
        compute: function() {
            var padBottomVal = 0;
            if (this.parent instanceof eqEd.StackedFractionNumeratorContainer) {
                padBottomVal = 0.2;
            }
            return padBottomVal;
        },
        updateDom: function() {}
    }));

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            var widthVal = this.radical.width + this.nthRootDiagonal.width + this.radicandContainer.width;
            if (this.nthRootDegreeContainer.isLeftFlushToWrapper) {
                widthVal += this.nthRootDegreeContainer.width - this.nthRootDegreeContainer.offsetRadicalRight * fontHeight + this.nthRootDegreeContainer.diagonalHeightAdjustment * this.nthRootDiagonal.height - this.radical.width;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
        	var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            var topAlignVal = 0;
             if (this.radicandContainer.wrappers.length > 0) {
                topAlignVal += this.radicandContainer.wrappers[this.radicandContainer.maxTopAlignIndex].topAlign;
            }
            if (this.radicandContainer.isMaxTopAlignRootWrapper) {
                topAlignVal += this.radicandContainer.padTopMaxChildAlignTopIsRoot * fontHeight;
            } else {
                topAlignVal += this.radicandContainer.padTopMaxChildAlignTopIsNotRoot * fontHeight;
            }
            if (this.nthRootDegreeContainer.isTopFlushToWrapper) {
                topAlignVal += this.nthRootDegreeContainer.height + this.radical.height + this.nthRootDegreeContainer.offsetRadicalBottom * fontHeight - this.nthRootDiagonal.height;
            }
            return topAlignVal;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
        	var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            var bottomAlignVal = 0;
            if (this.radicandContainer.wrappers.length > 0) {
                bottomAlignVal += this.radicandContainer.wrappers[this.radicandContainer.maxBottomAlignIndex].bottomAlign;
            }
            if (this.radicandContainer.isMaxTopAlignRootWrapper) {
                bottomAlignVal += this.radicandContainer.padBottomMaxChildAlignTopIsRoot * fontHeight;
            } else {
                bottomAlignVal += this.radicandContainer.padBottomMaxChildAlignTopIsNotRoot * fontHeight;
            }
            return bottomAlignVal;
        },
        updateDom: function() {}
    }));
};

(function() {
    // subclass extends superclass
    eqEd.NthRootWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.NthRootWrapper.prototype.constructor = eqEd.NthRootWrapper;
    eqEd.NthRootWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper nthRootWrapper"></div>')
    };
    eqEd.NthRootWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation);

        copy.domObj = copy.buildDomObj();    

        copy.radicandContainer = this.radicandContainer.clone();
        copy.radicandContainer.parent = copy;
        copy.nthRootOverBar = this.nthRootOverBar.clone();
        copy.nthRootOverBar.parent = copy;
        copy.radical = this.radical.clone();
        copy.radical.parent = copy;
        copy.nthRootDiagonal = this.nthRootDiagonal.clone();
        copy.nthRootDiagonal.parent = copy;
        copy.nthRootDegreeContainer = this.nthRootDegreeContainer.clone();
        copy.nthRootDegreeContainer.parent = copy;
        copy.domObj.append(copy.radicandContainer.domObj);
        copy.domObj.append(copy.nthRootOverBar.domObj);
        copy.domObj.append(copy.radical.domObj);
        copy.domObj.append(copy.nthRootDiagonal.domObj);
        copy.domObj.append(copy.nthRootDegreeContainer.domObj);
        copy.childContainers = [copy.radicandContainer, copy.nthRootDegreeContainer];
        copy.childNoncontainers = [copy.nthRootDiagonal, copy.radical, copy.nthRootOverBar];

        return copy;
    };
    eqEd.NthRootWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: null,
            operands: {
                radicand: this.radicandContainer.buildJsonObj(),
                degree: this.nthRootDegreeContainer.buildJsonObj()
            }
        };
        return jsonObj;
    };
    eqEd.NthRootWrapper.constructFromJsonObj = function(jsonObj, equation) {
        var nthRootWrapper = new eqEd.NthRootWrapper(equation);
        for (var i = 0; i < jsonObj.operands.radicand.length; i++) {
            var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.radicand[i].type);
            var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.radicand[i], equation);
            nthRootWrapper.radicandContainer.addWrappers([i, innerWrapper]);
        }
        for (var i = 0; i < jsonObj.operands.degree.length; i++) {
            var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.degree[i].type);
            var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.degree[i], equation);
            nthRootWrapper.nthRootDegreeContainer.addWrappers([i, innerWrapper]);
        }
        return nthRootWrapper;
    }
})();

/* End eq/js/equation-components/wrappers/nthRootWrapper.js*/

/* Begin eq/js/equation-components/misc/nthRootOverBar.js*/

eqEd.NthRootOverBar = function(parent) {
    eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
    this.className = "eqEd.NthRootOverBar";

    this.domObj = this.buildDomObj();
    this.adjustLeft = -0.06;
    this.heightRatio = 0.055;

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return this.parent.radicandContainer.width - this.adjustLeft * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height["fontSizeNormal"];
            return this.heightRatio * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftVal = this.parent.radical.width + this.parent.nthRootDiagonal.width;
            if (this.parent.nthRootDegreeContainer.isLeftFlushToWrapper) {
                leftVal += this.parent.nthRootDegreeContainer.width - this.parent.nthRootDegreeContainer.offsetRadicalRight * fontHeight + this.parent.nthRootDegreeContainer.diagonalHeightAdjustment * this.parent.nthRootDiagonal.height - this.parent.radical.width;
            }
            return leftVal;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var topVal = 0;
            if (this.parent.nthRootDegreeContainer.isTopFlushToWrapper) {
                topVal += this.parent.nthRootDegreeContainer.height + this.parent.radical.height + this.parent.nthRootDegreeContainer.offsetRadicalBottom * fontHeight - this.parent.nthRootDiagonal.height;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.NthRootOverBar.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.NthRootOverBar.prototype.constructor = eqEd.NthRootOverBar;
    eqEd.NthRootOverBar.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="nthRootOverBar"></div>');
    };
})();

/* End eq/js/equation-components/misc/nthRootOverBar.js*/

/* Begin eq/js/equation-components/misc/nthRootRadical.js*/

eqEd.NthRootRadical = function(parent) {
    eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
    this.className = "eqEd.NthRootRadical";

    this.domObj = this.buildDomObj();

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var widthVal = 0;
            if (this.parent.nthRootDiagonal.height < 2 * fontHeight) {
                widthVal = 0.4 * fontHeight;
            } else {
                widthVal = 0.5 * fontHeight;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var heightVal = 0;
            if (this.parent.nthRootDiagonal.height < 2 * fontHeight) {
                heightVal = 0.7 * fontHeight;
            } else {
                heightVal = 0.75 * fontHeight;
            }
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftVal = 0;
            if (this.parent.nthRootDegreeContainer.isLeftFlushToWrapper) {
                leftVal += this.parent.nthRootDegreeContainer.width - this.parent.nthRootDegreeContainer.offsetRadicalRight * fontHeight + this.parent.nthRootDegreeContainer.diagonalHeightAdjustment * this.parent.nthRootDiagonal.height - this.parent.radical.width;
            }
            return leftVal;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var topVal = this.parent.nthRootDiagonal.height - this.height;
            if (this.parent.nthRootDegreeContainer.isTopFlushToWrapper) {
                topVal += this.parent.nthRootDegreeContainer.height + this.parent.radical.height + this.parent.nthRootDegreeContainer.offsetRadicalBottom * fontHeight - this.parent.nthRootDiagonal.height;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.NthRootRadical.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.NthRootRadical.prototype.constructor = eqEd.NthRootRadical;
    eqEd.NthRootRadical.prototype.buildDomObj = function() {
        var htmlRep = '<div class="nthRootRadical" style="width: 74.842293px; height: 127.48769px;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 74.842293 127.48769" preserveAspectRatio="none"><defs id="defs4"><clipPath clipPathUnits="userSpaceOnUse" id="clipPath3765"><rect style="fill:#b1ded2;fill-opacity:1;stroke:none" id="rect3767" width="74.842293" height="127.62585" x="198.84776" y="668.99451" /></clipPath></defs><g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1" transform="translate(-198.84776,-668.99451)"><g clip-path="url(#clipPath3765)"><path d="m 265.30006,796.48219 -47.75994,-111.23309 -14.88621,11.47479 -3.82493,-3.82493 30.28931,-23.46646 44.65864,103.89336 109.8892,-228.9789 c 0.68896,-1.30943 1.89502,-1.96414 3.61817,-1.96415 1.17139,1e-5 2.17069,0.41351 2.99792,1.24052 0.8268,0.82701 1.2403,1.82632 1.24052,2.99791 -2.2e-4,0.68918 -0.0691,1.17161 -0.20676,1.44728 L 273.15667,794.51804 c -0.55144,1.30919 -1.61966,1.96391 -3.20467,1.96415 l -4.65194,0" style="" id="path2987" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/nthRootRadical.js*/

/* Begin eq/js/equation-components/misc/nthRootDiagonal.js*/

eqEd.NthRootDiagonal = function(parent) {
    eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
    this.className = "eqEd.NthRootDiagonal";

    this.domObj = this.buildDomObj();
    this.adjustLeft = -0.035;

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 0.58 * fontHeight + 0.05 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var heightVal = this.parent.radicandContainer.height;
            if (this.parent.radicandContainer.isMaxTopAlignRootWrapper) {
                heightVal += (this.parent.radicandContainer.padTopMaxChildAlignTopIsRoot + this.parent.radicandContainer.padBottomMaxChildAlignTopIsRoot) * fontHeight;
            } else {
                heightVal += (this.parent.radicandContainer.padTopMaxChildAlignTopIsNotRoot + this.parent.radicandContainer.padBottomMaxChildAlignTopIsNotRoot) * fontHeight;
            }
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftVal = this.parent.radical.width;
            if (this.parent.nthRootDegreeContainer.isLeftFlushToWrapper) {
               leftVal += this.parent.nthRootDegreeContainer.width - this.parent.nthRootDegreeContainer.offsetRadicalRight * fontHeight + this.parent.nthRootDegreeContainer.diagonalHeightAdjustment * this.parent.nthRootDiagonal.height - this.parent.radical.width;
            }
            return leftVal;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var topVal = 0;
            if (this.parent.nthRootDegreeContainer.isTopFlushToWrapper) {
                topVal += this.parent.nthRootDegreeContainer.height + this.parent.radical.height + this.parent.nthRootDegreeContainer.offsetRadicalBottom * fontHeight - this.height;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.NthRootDiagonal.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.NthRootDiagonal.prototype.constructor = eqEd.NthRootDiagonal;
    eqEd.NthRootDiagonal.prototype.buildDomObj = function() {
        var htmlRep = '<div class="nthRootDiagonal" style="width: 130.0331px; height: 256.45282px;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 130.0331 256.45282" preserveAspectRatio="none"><g transform="translate(-391.39675,-547.35338)"><g transform="scale(1.1433177,0.87464752)" style="font-size:162.99891663px;font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;line-height:125%;letter-spacing:0px;word-spacing:0px;fill:#000000;fill-opacity:1;stroke:none;font-family:cmex10;-inkscape-font-specification:cmex10" id="text2989"><path d="m 342.3342,915.58351 0,-0.95508 c 0.053,-0.1064 0.0796,-0.21252 0.0796,-0.31835 0.053,-0.1064 0.0796,-0.21252 0.0796,-0.31836 L 448.42675,628.42526 c 0.42437,-1.37954 1.51208,-2.25503 3.26317,-2.62645 l 0.95507,0 c 1.75084,0.31836 2.89162,1.45914 3.42234,3.42234 l 0,0.95507 c -1.2e-4,0.10612 -0.0267,0.23877 -0.0796,0.39795 -1.2e-4,0.10612 -0.0266,0.21224 -0.0796,0.31836 L 349.97477,916.45899 c -0.42449,1.3262 -1.51221,2.17515 -3.26316,2.54686 l -0.95507,0 c -1.75098,-0.31865 -2.89176,-1.45943 -3.42234,-3.42234" style="" id="path2987" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/nthRootDiagonal.js*/

/* Begin eq/js/equation-components/containers/nthRootRadicandContainer.js*/

eqEd.NthRootRadicandContainer = function(parent) {
	eqEd.Container.call(this, parent);
	this.className = "eqEd.NthRootRadicandContainer";

	this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);
    this.padTopMaxChildAlignTopIsRoot = 0.45;
    this.padTopMaxChildAlignTopIsNotRoot = 0.15;
    this.padBottomMaxChildAlignTopIsRoot = 0.2;
    this.padBottomMaxChildAlignTopIsNotRoot = 0;
    
    // Set up the isMaxTopAlignRootWrapper calculation
    var isMaxTopAlignRootWrapper = false;
    this.properties.push(new Property(this, "isMaxTopAlignRootWrapper", isMaxTopAlignRootWrapper, {
        get: function() {
            return isMaxTopAlignRootWrapper;
        },
        set: function(value) {
            isMaxTopAlignRootWrapper = value;
        },
        compute: function() {
            var maxTopAlignIndexWrapper = this.wrappers[this.maxTopAlignIndex];
            return (maxTopAlignIndexWrapper instanceof eqEd.SquareRootWrapper || maxTopAlignIndexWrapper instanceof eqEd.NthRootWrapper);
        },
        updateDom: function() {}
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftVal = this.parent.radical.width + this.parent.nthRootDiagonal.width;
            if (this.parent.nthRootDegreeContainer.isLeftFlushToWrapper) {
                leftVal += this.parent.nthRootDegreeContainer.width - this.parent.nthRootDegreeContainer.offsetRadicalRight * fontHeight + this.parent.nthRootDegreeContainer.diagonalHeightAdjustment * this.parent.nthRootDiagonal.height - this.parent.radical.width;
            }
            return leftVal;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
        	var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var topVal = 0;
            if (this.isMaxTopAlignRootWrapper) {
                topVal += this.padTopMaxChildAlignTopIsRoot * fontHeight;
            } else {
                topVal += this.padTopMaxChildAlignTopIsNotRoot * fontHeight;
            }
            if (this.parent.nthRootDegreeContainer.isTopFlushToWrapper) {
                topVal += this.parent.nthRootDegreeContainer.height + this.parent.radical.height + this.parent.nthRootDegreeContainer.offsetRadicalBottom * fontHeight - this.parent.nthRootDiagonal.height;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
	        return actualParentContainer.fontSize;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};

(function() {
    // subclass extends superclass
    eqEd.NthRootRadicandContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.NthRootRadicandContainer.prototype.constructor = eqEd.NthRootRadicandContainer;
    eqEd.NthRootRadicandContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer nthRootRadicandContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/nthRootRadicandContainer.js*/

/* Begin eq/js/equation-components/containers/nthRootDegreeContainer.js*/

eqEd.NthRootDegreeContainer = function(parent) {
	eqEd.Container.call(this, parent);
	this.className = "eqEd.NthRootDegreeContainer";

	this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);
    this.offsetRadicalBottom = -0.1;
    this.offsetRadicalRight = 0.3;
    this.diagonalHeightAdjustment = 0.048; // Was 0.05, but didn't format quite right.
    
    // Set up the isLeftFlushToWrapper calculation
    var isLeftFlushToWrapper = false;
    this.properties.push(new Property(this, "isLeftFlushToWrapper", isLeftFlushToWrapper, {
        get: function() {
            return isLeftFlushToWrapper;
        },
        set: function(value) {
            isLeftFlushToWrapper = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var diagonalHeight = this.parent.radicandContainer.height;
            var isLeftFlushToWrapperVal = true;
            if (this.width - this.offsetRadicalRight * fontHeight + this.diagonalHeightAdjustment * diagonalHeight < this.parent.radical.width) {
                isLeftFlushToWrapperVal = false;
            }
            return isLeftFlushToWrapperVal;
        },
        updateDom: function() {}
    }));

    // Set up the isTopFlushToWrapper calculation
    var isTopFlushToWrapper = false;
    this.properties.push(new Property(this, "isTopFlushToWrapper", isTopFlushToWrapper, {
        get: function() {
            return isTopFlushToWrapper;
        },
        set: function(value) {
            isTopFlushToWrapper = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var diagonalHeight = this.parent.radicandContainer.height;
            var isTopFlushToWrapperVal = false;
            if (diagonalHeight - (this.parent.radical.height + this.offsetRadicalBottom * fontHeight) < this.height) {
                isTopFlushToWrapperVal = true;
            }
            return isTopFlushToWrapperVal;
        },
        updateDom: function() {}
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftVal = 0;
            if (!this.isLeftFlushToWrapper) {
                leftVal += this.parent.radical.width - (this.width - this.offsetRadicalRight * fontHeight + this.diagonalHeightAdjustment * this.parent.nthRootDiagonal.height);
            }
            return leftVal;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
        	var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var topVal = 0;
            if (!this.isTopFlushToWrapper) {
                topVal += this.parent.nthRootDiagonal.height - this.parent.radical.height - this.offsetRadicalBottom * fontHeight - this.height;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
	        return "fontSizeSmallest";
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};

(function() {
    // subclass extends superclass
    eqEd.NthRootDegreeContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.NthRootDegreeContainer.prototype.constructor = eqEd.NthRootDegreeContainer;
    eqEd.NthRootDegreeContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer nthRootDegreeContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/nthRootDegreeContainer.js*/

/* Begin eq/js/equation-components/wrappers/bracketWrapper.js*/

eqEd.BracketWrapper = function(equation, bracketType) {
	eqEd.Wrapper.call(this, equation); // call super constructor.
	this.className = "eqEd.BracketWrapper";

    this.bracketType = bracketType;
    var bracketCtors = {
        "leftParenthesisBracket": eqEd.LeftParenthesisBracket,
        "rightParenthesisBracket": eqEd.RightParenthesisBracket,
        "leftSquareBracket": eqEd.LeftSquareBracket,
        "rightSquareBracket": eqEd.RightSquareBracket,
        "leftCurlyBracket": eqEd.LeftCurlyBracket,
        "rightCurlyBracket": eqEd.RightCurlyBracket,
        "leftAngleBracket": eqEd.LeftAngleBracket,
        "rightAngleBracket": eqEd.RightAngleBracket,
        "leftFloorBracket": eqEd.LeftFloorBracket,
        "rightFloorBracket": eqEd.RightFloorBracket,
        "leftCeilBracket": eqEd.LeftCeilBracket,
        "rightCeilBracket": eqEd.RightCeilBracket
    };

    this.domObj = this.buildDomObj();

    this.bracket = new bracketCtors[bracketType](this);
    this.domObj.append(this.bracket.domObj);

    this.childNoncontainers = [this.bracket];

    this.padTop = 0.05;
    this.padBottom = 0.15;

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            return this.bracket.width;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
            var topAlignVal = 0;
            
            if (this.bracket.heightRatio <= 1.5) {
                topAlignVal = 0.525 * this.bracket.height;
            } else {
                topAlignVal = 0.5 * this.bracket.height;
            }
            
            return topAlignVal;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
            var bottomAlignVal = 0;
            
            if (this.bracket.heightRatio <= 1.5) {
                bottomAlignVal = 0.475 * this.bracket.height;
            } else {
                bottomAlignVal = 0.5 * this.bracket.height;
            }
            
            return bottomAlignVal;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.BracketWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.BracketWrapper.prototype.constructor = eqEd.BracketWrapper;
    eqEd.BracketWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper bracketWrapper ' + this.bracketType + '"></div>')
    };
    eqEd.BracketWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation, this.bracketType);
        return copy;
    };
    eqEd.BracketWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: this.bracketType,
            operands: null
        };
        return jsonObj;
    };
    eqEd.BracketWrapper.constructFromJsonObj = function(jsonObj, equation) {
      var bracketWrapper = new eqEd.BracketWrapper(equation, jsonObj.value);
      return bracketWrapper;
    }
})();

/* End eq/js/equation-components/wrappers/bracketWrapper.js*/

/* Begin eq/js/equation-components/misc/bracket.js*/

eqEd.Bracket = function(parent) {
	eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
	this.className = "eqEd.Bracket";

    if (IEVersion >= 9) {
        this.adjustTop = 0.280;
    } else {
        this.adjustTop = 0.025;
    }

    // Set up the heightRatio calculation
    var heightRatio = 0;
    this.properties.push(new Property(this, "heightRatio", heightRatio, {
        get: function() {
            return heightRatio;
        },
        set: function(value) {
            heightRatio = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return this.desiredHeight / fontHeight;
        },
        updateDom: function() {
        	// Not only a DOM update, but this is a convenient callback.
        	this.updateBracketStructure();
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var topVal = 0;
            if (this.parent instanceof eqEd.BracketPairWrapper) {
                if (this.parent.bracketContainer.wrappers.length > 0) {
                    var containerTopAlign = this.parent.bracketContainer.wrappers[this.parent.bracketContainer.maxTopAlignIndex].topAlign;
                    var bracketTopAlign = 0.5 * this.height;
                    if (bracketTopAlign < containerTopAlign) {
                        topVal = containerTopAlign - bracketTopAlign;
                    }
                }
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var leftVal = 0;
            if (this.parent instanceof eqEd.BracketPairWrapper && this instanceof eqEd.RightBracket) {
                leftVal = this.parent.leftBracket.width + this.parent.bracketContainer.width;
            }
            return leftVal;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.Bracket.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.Bracket.prototype.constructor = eqEd.Bracket;
    eqEd.Bracket.prototype.clone = function() {
        var copy = new this.constructor(this.parent);
        copy.domObj = copy.buildDomObj();
        if (this.wholeBracket !== null) {
            copy.wholeBracket = this.wholeBracket.clone();
            copy.wholeBracket.parent = copy;
            copy.domObj.append(copy.wholeBracket.domObj);
            copy.children.push(copy.wholeBracket);
        } else {
            copy.wholeBracket = null;
        }
        if (this.topBracket !== null) {
            copy.topBracket = this.topBracket.clone();
            copy.topBracket.parent = copy;
            copy.domObj.append(copy.topBracket.domObj);
            copy.children.push(copy.topBracket);
        } else {
            copy.topBracket = null;
        }
        copy.middleBrackets = [];
        for (var i = 0; i < this.middleBrackets.length; i++) {
            var middleBracket = this.middleBrackets[i].clone();
            middleBracket.parent = copy;
            copy.domObj.append(middleBracket.domObj);
            copy.middleBrackets.push(middleBracket);
            copy.children.push(copy.middleBracket);
        }
        if (this.bottomBracket !== null) {
            copy.bottomBracket = this.bottomBracket.clone();
            copy.bottomBracket.parent = copy;
            copy.domObj.append(copy.bottomBracket.domObj);
            copy.children.push(copy.bottomBracket);
        } else {
            copy.bottomBracket = null;
        }

        this.childNoncontainers = [this.wholeBracket];

        return copy;
    };
})();

/* End eq/js/equation-components/misc/bracket.js*/

/* Begin eq/js/equation-components/misc/wholeBracket.js*/

eqEd.WholeBracket = function(parent) {
    eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
    this.className = "eqEd.WholeBracket";
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.WholeBracket.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.WholeBracket.prototype.constructor = eqEd.WholeBracket;
    eqEd.WholeBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="wholeBracket ' + this.fontStyle + '">' + this.character + '</div>');
    };
    eqEd.WholeBracket.prototype.clone = function() {
        var copy = new this.constructor(this.parent);
        return copy;
    };
})();

/* End eq/js/equation-components/misc/wholeBracket.js*/

/* Begin eq/js/equation-components/misc/topBracket.js*/

eqEd.TopBracket = function(parent) {
    eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
    this.className = "eqEd.TopBracket";
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.TopBracket.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.TopBracket.prototype.constructor = eqEd.TopBracket;
    eqEd.TopBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="topBracket ' + this.fontStyle + '">' + this.character + '</div>');
    };
    eqEd.TopBracket.prototype.clone = function() {
        var copy = new this.constructor(this.parent);
        return copy;
    };
})();

/* End eq/js/equation-components/misc/topBracket.js*/

/* Begin eq/js/equation-components/misc/middleBracket.js*/

eqEd.MiddleBracket = function(parent, index) {
    eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
    this.className = "eqEd.MiddleBracket";
    
    this.index = index;

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.MiddleBracket.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.MiddleBracket.prototype.constructor = eqEd.MiddleBracket;
    eqEd.MiddleBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="middleBracket ' + this.fontStyle + '">' + this.character + '</div>');
    };
    eqEd.MiddleBracket.prototype.clone = function() {
        var copy = new this.constructor(this.parent, this.index);
        return copy;
    };
})();

/* End eq/js/equation-components/misc/middleBracket.js*/

/* Begin eq/js/equation-components/misc/bottomBracket.js*/

eqEd.BottomBracket = function(parent) {
    eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
    this.className = "eqEd.BottomBracket";

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.BottomBracket.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.BottomBracket.prototype.constructor = eqEd.BottomBracket;
    eqEd.BottomBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="bottomBracket ' + this.fontStyle + '">' + this.character + '</div>');
    };
    eqEd.BottomBracket.prototype.clone = function() {
        var copy = new this.constructor(this.parent);
        return copy;
    };
})();

/* End eq/js/equation-components/misc/bottomBracket.js*/

/* Begin eq/js/equation-components/misc/leftBracket.js*/

eqEd.LeftBracket = function(parent) {
	eqEd.Bracket.call(this, parent); // call super constructor.
	this.className = "eqEd.LeftBracket";

    // Set up the desiredHeight calculation
    var desiredHeight = 0;
    this.properties.push(new Property(this, "desiredHeight", desiredHeight, {
        get: function() {
            return desiredHeight;
        },
        set: function(value) {
            desiredHeight = value;
        },
        compute: function() {
            var desiredHeightVal = 0;
            if (this.parent instanceof eqEd.BracketWrapper) {
                var sameBracketTypeCounter = 0;
                var matchingBracketIndex = null;
                var maxTopAlign = 0;
                var maxBottomAlign = 0;
                for (var i = (this.parent.index + 1); i < this.parent.parent.wrappers.length; i++) {
                    var wrapper = this.parent.parent.wrappers[i];
                    if (wrapper instanceof eqEd.BracketWrapper) {
                        if (wrapper.bracket instanceof this.constructor) {
                            sameBracketTypeCounter++;
                        } else if (wrapper.bracket instanceof this.matchingBracketCtor 
                                    && sameBracketTypeCounter === 0) {
                            matchingBracketIndex = i;
                            break;
                        } else if (wrapper.bracket instanceof this.matchingBracketCtor) {
                            sameBracketTypeCounter--;
                        }
                    } else {
                        maxTopAlign = (wrapper.topAlign > maxTopAlign) ? wrapper.topAlign : maxTopAlign;
                        maxBottomAlign = (wrapper.bottomAlign > maxBottomAlign) ? wrapper.bottomAlign : maxBottomAlign;
                    }
                }
                if (matchingBracketIndex !== null && !(maxTopAlign === 0 && maxBottomAlign === 0)) {
                    desiredHeightVal = (maxTopAlign > maxBottomAlign) ? 2 * maxTopAlign : 2 * maxBottomAlign;
                } else {
                    var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
                    desiredHeightVal = fontHeight;
                }
            } else if (this.parent instanceof eqEd.BracketPairWrapper) {
                if (this.parent.bracketContainer.wrappers.length > 0) {
                    var maxTopAlign = this.parent.bracketContainer.wrappers[this.parent.bracketContainer.maxTopAlignIndex].topAlign;
                    var maxBottomAlign = this.parent.bracketContainer.wrappers[this.parent.bracketContainer.maxBottomAlignIndex].bottomAlign;
                    desiredHeightVal = (maxTopAlign > maxBottomAlign) ? 2 * maxTopAlign : 2 * maxBottomAlign;
                }
                
            }
            return desiredHeightVal;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftBracket.prototype = Object.create(eqEd.Bracket.prototype);
    eqEd.LeftBracket.prototype.constructor = eqEd.LeftBracket;
})();

/* End eq/js/equation-components/misc/leftBracket.js*/

/* Begin eq/js/equation-components/misc/rightBracket.js*/

eqEd.RightBracket = function(parent) {
	eqEd.Bracket.call(this, parent); // call super constructor.
	this.className = "eqEd.RightBracket";

    // Set up the desiredHeight calculation
    var desiredHeight = 0;
    this.properties.push(new Property(this, "desiredHeight", desiredHeight, {
        get: function() {
            return desiredHeight;
        },
        set: function(value) {
            desiredHeight = value;
        },
        compute: function() {
            var desiredHeightVal = 0;
            if (this.parent instanceof eqEd.BracketWrapper) {
                var sameBracketTypeCounter = 0;
                var matchingBracketIndex = null;
                var maxTopAlign = 0;
                var maxBottomAlign = 0;
                for (var i = (this.parent.index - 1); i >= 0; i--) {
                    var wrapper = this.parent.parent.wrappers[i];
                    if (wrapper instanceof eqEd.BracketWrapper) {
                        if (wrapper.bracket instanceof this.constructor) {
                            sameBracketTypeCounter++;
                        } else if (wrapper.bracket instanceof this.matchingBracketCtor 
                                    && sameBracketTypeCounter === 0) {
                            matchingBracketIndex = i;
                            break;
                        } else if (wrapper.bracket instanceof this.matchingBracketCtor) {
                            sameBracketTypeCounter--;
                        }
                    } else {
                        maxTopAlign = (wrapper.topAlign > maxTopAlign) ? wrapper.topAlign : maxTopAlign;
                        maxBottomAlign = (wrapper.bottomAlign > maxBottomAlign) ? wrapper.bottomAlign : maxBottomAlign;
                    }
                }
                if (matchingBracketIndex !== null && !(maxTopAlign === 0 && maxBottomAlign === 0)) {
                    desiredHeightVal = (maxTopAlign > maxBottomAlign) ? 2 * maxTopAlign : 2 * maxBottomAlign;
                } else {
                    var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
                    desiredHeightVal = fontHeight;
                }
            } else if (this.parent instanceof eqEd.BracketPairWrapper) {
                if (this.parent.bracketContainer.wrappers.length > 0) { 
                    var maxTopAlign = this.parent.bracketContainer.wrappers[this.parent.bracketContainer.maxTopAlignIndex].topAlign;
                    var maxBottomAlign = this.parent.bracketContainer.wrappers[this.parent.bracketContainer.maxBottomAlignIndex].bottomAlign;
                    desiredHeightVal = (maxTopAlign > maxBottomAlign) ? 2 * maxTopAlign : 2 * maxBottomAlign;
                }
            }
            return desiredHeightVal;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightBracket.prototype = Object.create(eqEd.Bracket.prototype);
    eqEd.RightBracket.prototype.constructor = eqEd.RightBracket;
})();

/* End eq/js/equation-components/misc/rightBracket.js*/

/* Begin eq/js/equation-components/misc/leftParenthesisBracket.js*/

eqEd.LeftParenthesisBracket = function(parent) {
	eqEd.LeftBracket.call(this, parent); // call super constructor.
	this.className = "eqEd.LeftParenthesisBracket";

    this.matchingBracketCtor = eqEd.RightParenthesisBracket;
    this.wholeBracket = new eqEd.LeftParenthesisWholeBracket(parent, "MathJax_Main");
    this.topBracket = null;
    this.middleBrackets = [];
    this.bottomBracket = null;

    this.wholeBracket.parent = this;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.wholeBracket.domObj);

    this.children = [this.wholeBracket];

	// Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var widthVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                widthVal = 0.377777 * fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                widthVal = 0.733333 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                widthVal = 0.777777 * fontHeight;
            } else if (this.heightRatio > 3 && this.heightRatio <= 3.33) {
                widthVal = 0.88888 * fontHeight;
            } else {
                widthVal = 0.88888 * fontHeight;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var heightVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                heightVal = fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                heightVal = 2.4 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                heightVal = 3 * fontHeight;
            } else if (this.heightRatio > 3 && this.heightRatio <= 3.33) {
                heightVal = 3.33 * fontHeight;
            } else {
                heightVal = (3.9 + (0.45 * (this.middleBrackets.length - 1))) * fontHeight;
            }
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftParenthesisBracket.prototype = Object.create(eqEd.LeftBracket.prototype);
    eqEd.LeftParenthesisBracket.prototype.constructor = eqEd.LeftParenthesisBracket;
    eqEd.LeftParenthesisBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="bracket leftBracket leftParenthesisBracket"></div>')
    };
    // This is a callback that happens after this.heightRation gets calculated.
    eqEd.LeftParenthesisBracket.prototype.updateBracketStructure = function() {
        this.domObj.empty();
        this.wholeBracket = null;
        this.topBracket = null;
        this.middleBrackets = [];
        this.bottomBracket = null;
        this.children = [];
        if (this.heightRatio <= 1.5) {
            this.wholeBracket = new eqEd.LeftParenthesisWholeBracket(this.parent, "MathJax_Main");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
            this.wholeBracket = new eqEd.LeftParenthesisWholeBracket(this.parent, "MathJax_Size3");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
            this.wholeBracket = new eqEd.LeftParenthesisWholeBracket(this.parent, "MathJax_Size4");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 3 && this.heightRatio <= 3.33) {
            this.topBracket = new eqEd.LeftParenthesisTopBracket(this.parent);
            this.bottomBracket = new eqEd.LeftParenthesisBottomBracket(this.parent);
            this.topBracket.parent = this;
            this.bottomBracket.parent = this;
            this.domObj.append(this.topBracket.domObj);
            this.domObj.append(this.bottomBracket.domObj);
            this.children = [this.topBracket, this.bottomBracket];
        } else {
            var numberOfMiddleBrackets = Math.ceil((this.heightRatio - 3.9)/0.45) + 1;
            this.topBracket = new eqEd.LeftParenthesisTopBracket(this.parent);
            this.bottomBracket = new eqEd.LeftParenthesisBottomBracket(this.parent);
            this.topBracket.parent = this;
            this.bottomBracket.parent = this;
            this.domObj.append(this.topBracket.domObj);
            this.domObj.append(this.bottomBracket.domObj);
            for (var i = 0; i < numberOfMiddleBrackets; i++) {
                var middleBracket = new eqEd.LeftParenthesisMiddleBracket(this.parent, i);
                middleBracket.parent = this;
                this.domObj.append(middleBracket.domObj);
                this.middleBrackets.push(middleBracket);
            }
            this.children = [this.topBracket].concat(this.middleBrackets).concat([this.bottomBracket]);
        }
    }
})();

/* End eq/js/equation-components/misc/leftParenthesisBracket.js*/

/* Begin eq/js/equation-components/misc/rightParenthesisBracket.js*/

eqEd.RightParenthesisBracket = function(parent) {
    eqEd.RightBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightParenthesisBracket";

    this.matchingBracketCtor = eqEd.LeftParenthesisBracket;
    this.wholeBracket = new eqEd.RightParenthesisWholeBracket(parent, "MathJax_Main");
    this.topBracket = null;
    this.middleBrackets = [];
    this.bottomBracket = null;

    this.wholeBracket.parent = this;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.wholeBracket.domObj);

    this.children = [this.wholeBracket];

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var widthVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                widthVal = 0.377777 * fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                widthVal = 0.733333 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                widthVal = 0.777777 * fontHeight;
            } else if (this.heightRatio > 3 && this.heightRatio <= 3.33) {
                widthVal = 0.88888 * fontHeight;
            } else {
                widthVal = 0.88888 * fontHeight;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var heightVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                heightVal = fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                heightVal = 2.4 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                heightVal = 3 * fontHeight;
            } else if (this.heightRatio > 3 && this.heightRatio <= 3.33) {
                heightVal = 3.33 * fontHeight;
            } else {
                heightVal = (3.9 + (0.45 * (this.middleBrackets.length - 1))) * fontHeight;
            }
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightParenthesisBracket.prototype = Object.create(eqEd.RightBracket.prototype);
    eqEd.RightParenthesisBracket.prototype.constructor = eqEd.RightParenthesisBracket;
    eqEd.RightParenthesisBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="bracket rightBracket rightParenthesisBracket"></div>')
    };
    // This is a callback that happens after this.heightRation gets calculated.
    eqEd.RightParenthesisBracket.prototype.updateBracketStructure = function() {
        this.domObj.empty();
        this.wholeBracket = null;
        this.topBracket = null;
        this.middleBrackets = [];
        this.bottomBracket = null;
        this.children = [];
        if (this.heightRatio <= 1.5) {
            this.wholeBracket = new eqEd.RightParenthesisWholeBracket(this.parent, "MathJax_Main");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
            this.wholeBracket = new eqEd.RightParenthesisWholeBracket(this.parent, "MathJax_Size3");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
            this.wholeBracket = new eqEd.RightParenthesisWholeBracket(this.parent, "MathJax_Size4");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 3 && this.heightRatio <= 3.33) {
            this.topBracket = new eqEd.RightParenthesisTopBracket(this.parent);
            this.bottomBracket = new eqEd.RightParenthesisBottomBracket(this.parent);
            this.topBracket.parent = this;
            this.bottomBracket.parent = this;
            this.domObj.append(this.topBracket.domObj);
            this.domObj.append(this.bottomBracket.domObj);
            this.children = [this.topBracket, this.bottomBracket];
        } else {
            var numberOfMiddleBrackets = Math.ceil((this.heightRatio - 3.9)/0.45) + 1;
            this.topBracket = new eqEd.RightParenthesisTopBracket(this.parent);
            this.bottomBracket = new eqEd.RightParenthesisBottomBracket(this.parent);
            this.topBracket.parent = this;
            this.bottomBracket.parent = this;
            this.domObj.append(this.topBracket.domObj);
            this.domObj.append(this.bottomBracket.domObj);
            for (var i = 0; i < numberOfMiddleBrackets; i++) {
                var middleBracket = new eqEd.RightParenthesisMiddleBracket(this.parent, i);
                middleBracket.parent = this;
                this.domObj.append(middleBracket.domObj);
                this.middleBrackets.push(middleBracket);
            }
            this.children = [this.topBracket].concat(this.middleBrackets).concat([this.bottomBracket]);
        }
    }
})();

/* End eq/js/equation-components/misc/rightParenthesisBracket.js*/

/* Begin eq/js/equation-components/misc/leftParenthesisWholeBracket.js*/

eqEd.LeftParenthesisWholeBracket = function(parent, fontStyle) {
    eqEd.WholeBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftParenthesisWholeBracket";
    
    this.character = "(";
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    this.adjustTop = 0;
    if (this.fontStyle === "MathJax_Main") {
        this.adjustTop = -0.0625;
    } else if (this.fontStyle === "MathJax_Size3") {
        this.adjustTop = 0.7;
    } else if (this.fontStyle === "MathJax_Size4") {
        this.adjustTop = 0.995;
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.LeftParenthesisWholeBracket.prototype = Object.create(eqEd.WholeBracket.prototype);
    eqEd.LeftParenthesisWholeBracket.prototype.constructor = eqEd.LeftParenthesisWholeBracket;
})();

/* End eq/js/equation-components/misc/leftParenthesisWholeBracket.js*/

/* Begin eq/js/equation-components/misc/leftParenthesisTopBracket.js*/

eqEd.LeftParenthesisTopBracket = function(parent) {
    eqEd.TopBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftParenthesisTopBracket";
    
    this.character = "⎛";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();
    this.adjustTop = 0.4;
};
(function() {
    // subclass extends superclass
    eqEd.LeftParenthesisTopBracket.prototype = Object.create(eqEd.TopBracket.prototype);
    eqEd.LeftParenthesisTopBracket.prototype.constructor = eqEd.LeftParenthesisTopBracket;
})();

/* End eq/js/equation-components/misc/leftParenthesisTopBracket.js*/

/* Begin eq/js/equation-components/misc/leftParenthesisMiddleBracket.js*/

eqEd.LeftParenthesisMiddleBracket = function(parent, index) {
    eqEd.MiddleBracket.call(this, parent, index); // call super constructor.
    this.className = "eqEd.LeftParenthesisMiddleBracket";
    
    this.character = "⎜";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            return (0.45 * this.index + 1.5) * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftParenthesisMiddleBracket.prototype = Object.create(eqEd.MiddleBracket.prototype);
    eqEd.LeftParenthesisMiddleBracket.prototype.constructor = eqEd.LeftParenthesisMiddleBracket;
})();

/* End eq/js/equation-components/misc/leftParenthesisMiddleBracket.js*/

/* Begin eq/js/equation-components/misc/leftParenthesisBottomBracket.js*/

eqEd.LeftParenthesisBottomBracket = function(parent) {
    eqEd.BottomBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftParenthesisBottomBracket";
    
    this.character = "⎝";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            var topVal = 0;
            if (this.parent.middleBrackets.length === 0) {
                topVal = 1.939 * fontHeight;
            } else {
                topVal = (2.5 + (0.45 * (this.parent.middleBrackets.length - 1))) * fontHeight;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftParenthesisBottomBracket.prototype = Object.create(eqEd.BottomBracket.prototype);
    eqEd.LeftParenthesisBottomBracket.prototype.constructor = eqEd.LeftParenthesisBottomBracket;
})();

/* End eq/js/equation-components/misc/leftParenthesisBottomBracket.js*/

/* Begin eq/js/equation-components/misc/rightParenthesisWholeBracket.js*/

eqEd.RightParenthesisWholeBracket = function(parent, fontStyle) {
    eqEd.WholeBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightParenthesisWholeBracket";
    
    this.character = ")";
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    this.adjustTop = 0;
    if (this.fontStyle === "MathJax_Main") {
        this.adjustTop = -0.0625;
    } else if (this.fontStyle === "MathJax_Size3") {
        this.adjustTop = 0.7;
    } else if (this.fontStyle === "MathJax_Size4") {
        this.adjustTop = 0.995;
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.RightParenthesisWholeBracket.prototype = Object.create(eqEd.WholeBracket.prototype);
    eqEd.RightParenthesisWholeBracket.prototype.constructor = eqEd.RightParenthesisWholeBracket;
})();

/* End eq/js/equation-components/misc/rightParenthesisWholeBracket.js*/

/* Begin eq/js/equation-components/misc/rightParenthesisTopBracket.js*/

eqEd.RightParenthesisTopBracket = function(parent) {
    eqEd.TopBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightParenthesisTopBracket";
    
    this.character = "⎞";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();
    this.adjustTop = 0.4;
};
(function() {
    // subclass extends superclass
    eqEd.RightParenthesisTopBracket.prototype = Object.create(eqEd.TopBracket.prototype);
    eqEd.RightParenthesisTopBracket.prototype.constructor = eqEd.RightParenthesisTopBracket;
})();

/* End eq/js/equation-components/misc/rightParenthesisTopBracket.js*/

/* Begin eq/js/equation-components/misc/rightParenthesisMiddleBracket.js*/

eqEd.RightParenthesisMiddleBracket = function(parent, index) {
    eqEd.MiddleBracket.call(this, parent, index); // call super constructor.
    this.className = "eqEd.RightParenthesisMiddleBracket";
    
    this.character = "⎟";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            return (0.45 * this.index + 1.5) * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightParenthesisMiddleBracket.prototype = Object.create(eqEd.MiddleBracket.prototype);
    eqEd.RightParenthesisMiddleBracket.prototype.constructor = eqEd.RightParenthesisMiddleBracket;
})();

/* End eq/js/equation-components/misc/rightParenthesisMiddleBracket.js*/

/* Begin eq/js/equation-components/misc/rightParenthesisBottomBracket.js*/

eqEd.RightParenthesisBottomBracket = function(parent) {
    eqEd.BottomBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightParenthesisBottomBracket";
    
    this.character = "⎠";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            var topVal = 0;
            if (this.parent.middleBrackets.length === 0) {
                topVal = 1.939 * fontHeight;
            } else {
                topVal = (2.5 + (0.45 * (this.parent.middleBrackets.length - 1))) * fontHeight;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightParenthesisBottomBracket.prototype = Object.create(eqEd.BottomBracket.prototype);
    eqEd.RightParenthesisBottomBracket.prototype.constructor = eqEd.RightParenthesisBottomBracket;
})();

/* End eq/js/equation-components/misc/rightParenthesisBottomBracket.js*/

/* Begin eq/js/equation-components/misc/leftSquareBracket.js*/

eqEd.LeftSquareBracket = function(parent) {
	eqEd.LeftBracket.call(this, parent); // call super constructor.
	this.className = "eqEd.LeftSquareBracket";

    this.matchingBracketCtor = eqEd.RightSquareBracket;
    this.wholeBracket = new eqEd.LeftSquareWholeBracket(parent, "MathJax_Main");
    this.topBracket = null;
    this.middleBrackets = [];
    this.bottomBracket = null;

    this.wholeBracket.parent = this;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.wholeBracket.domObj);

    this.children = [this.wholeBracket];

	// Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var widthVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                widthVal = 0.288888 * fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                widthVal = 0.533333 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                widthVal = 0.577777 * fontHeight;
            } else {
                widthVal = 0.666666 * fontHeight;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var heightVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                heightVal = fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                heightVal = 2.4 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                heightVal = 3 * fontHeight;
            } else {
                heightVal = (0.6 + (0.45 * (this.middleBrackets.length - 1))) * fontHeight;
            }
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftSquareBracket.prototype = Object.create(eqEd.LeftBracket.prototype);
    eqEd.LeftSquareBracket.prototype.constructor = eqEd.LeftSquareBracket;
    eqEd.LeftSquareBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="bracket leftBracket leftSquareBracket"></div>')
    };
    // This is a callback that happens after this.heightRation gets calculated.
    eqEd.LeftSquareBracket.prototype.updateBracketStructure = function() {
        this.domObj.empty();
        this.wholeBracket = null;
        this.topBracket = null;
        this.middleBrackets = [];
        this.bottomBracket = null;
        this.children = [];
        if (this.heightRatio <= 1.5) {
            this.wholeBracket = new eqEd.LeftSquareWholeBracket(this.parent, "MathJax_Main");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
            this.wholeBracket = new eqEd.LeftSquareWholeBracket(this.parent, "MathJax_Size3");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
            this.wholeBracket = new eqEd.LeftSquareWholeBracket(this.parent, "MathJax_Size4");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else {
            var numberOfMiddleBrackets = Math.ceil((this.heightRatio - 0.6)/0.45) + 1;
            this.topBracket = new eqEd.LeftSquareTopBracket(this.parent);
            this.bottomBracket = new eqEd.LeftSquareBottomBracket(this.parent);
            this.topBracket.parent = this;
            this.bottomBracket.parent = this;
            this.domObj.append(this.topBracket.domObj);
            this.domObj.append(this.bottomBracket.domObj);
            for (var i = 0; i < numberOfMiddleBrackets; i++) {
                var middleBracket = new eqEd.LeftSquareMiddleBracket(this.parent, i);
                middleBracket.parent = this;
                this.domObj.append(middleBracket.domObj);
                this.middleBrackets.push(middleBracket);
            }
            this.children = [this.topBracket].concat(this.middleBrackets).concat([this.bottomBracket]);
        }
    }
})();

/* End eq/js/equation-components/misc/leftSquareBracket.js*/

/* Begin eq/js/equation-components/misc/leftSquareWholeBracket.js*/

eqEd.LeftSquareWholeBracket = function(parent, fontStyle) {
    eqEd.WholeBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftSquareWholeBracket";
    
    this.character = "[";
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    this.adjustTop = 0;
    if (this.fontStyle === "MathJax_Main") {
        this.adjustTop = -0.0625;
    } else if (this.fontStyle === "MathJax_Size3") {
        this.adjustTop = 0.7;
    } else if (this.fontStyle === "MathJax_Size4") {
        this.adjustTop = 0.995;
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.LeftSquareWholeBracket.prototype = Object.create(eqEd.WholeBracket.prototype);
    eqEd.LeftSquareWholeBracket.prototype.constructor = eqEd.LeftSquareWholeBracket;
})();

/* End eq/js/equation-components/misc/leftSquareWholeBracket.js*/

/* Begin eq/js/equation-components/misc/leftSquareTopBracket.js*/

eqEd.LeftSquareTopBracket = function(parent) {
    eqEd.TopBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftSquareTopBracket";
    
    this.character = "⎡";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();
    this.adjustTop = 0.4;
};
(function() {
    // subclass extends superclass
    eqEd.LeftSquareTopBracket.prototype = Object.create(eqEd.TopBracket.prototype);
    eqEd.LeftSquareTopBracket.prototype.constructor = eqEd.LeftSquareTopBracket;
})();

/* End eq/js/equation-components/misc/leftSquareTopBracket.js*/

/* Begin eq/js/equation-components/misc/leftSquareMiddleBracket.js*/

eqEd.LeftSquareMiddleBracket = function(parent, index) {
    eqEd.MiddleBracket.call(this, parent, index); // call super constructor.
    this.className = "eqEd.LeftSquareMiddleBracket";
    
    this.character = "⎢";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            return (0.45 * this.index - 0.15) * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftSquareMiddleBracket.prototype = Object.create(eqEd.MiddleBracket.prototype);
    eqEd.LeftSquareMiddleBracket.prototype.constructor = eqEd.LeftSquareMiddleBracket;
})();

/* End eq/js/equation-components/misc/leftSquareMiddleBracket.js*/

/* Begin eq/js/equation-components/misc/leftSquareBottomBracket.js*/

eqEd.LeftSquareBottomBracket = function(parent) {
    eqEd.BottomBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftSquareBottomBracket";
    
    this.character = "⎣";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            var topVal = this.parent.middleBrackets[this.parent.middleBrackets.length - 1].top - 0.65 * fontHeight;
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftSquareBottomBracket.prototype = Object.create(eqEd.BottomBracket.prototype);
    eqEd.LeftSquareBottomBracket.prototype.constructor = eqEd.LeftSquareBottomBracket;
})();

/* End eq/js/equation-components/misc/leftSquareBottomBracket.js*/

/* Begin eq/js/equation-components/misc/rightSquareBracket.js*/

eqEd.RightSquareBracket = function(parent) {
    eqEd.RightBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightSquareBracket";

    this.matchingBracketCtor = eqEd.LeftSquareBracket;
    this.wholeBracket = new eqEd.RightSquareWholeBracket(parent, "MathJax_Main");
    this.topBracket = null;
    this.middleBrackets = [];
    this.bottomBracket = null;

    this.wholeBracket.parent = this;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.wholeBracket.domObj);

    this.children = [this.wholeBracket];

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var widthVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                widthVal = 0.288888 * fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                widthVal = 0.533333 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                widthVal = 0.577777 * fontHeight;
            } else {
                widthVal = 0.666666 * fontHeight;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var heightVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                heightVal = fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                heightVal = 2.4 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                heightVal = 3 * fontHeight;
            } else {
                heightVal = (0.6 + (0.45 * (this.middleBrackets.length - 1))) * fontHeight;
            }
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightSquareBracket.prototype = Object.create(eqEd.RightBracket.prototype);
    eqEd.RightSquareBracket.prototype.constructor = eqEd.RightSquareBracket;
    eqEd.RightSquareBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="bracket rightBracket rightSquareBracket"></div>')
    };
    // This is a callback that happens after this.heightRation gets calculated.
    eqEd.RightSquareBracket.prototype.updateBracketStructure = function() {
        this.domObj.empty();
        this.wholeBracket = null;
        this.topBracket = null;
        this.middleBrackets = [];
        this.bottomBracket = null;
        this.children = [];
        if (this.heightRatio <= 1.5) {
            this.wholeBracket = new eqEd.RightSquareWholeBracket(this.parent, "MathJax_Main");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
            this.wholeBracket = new eqEd.RightSquareWholeBracket(this.parent, "MathJax_Size3");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
            this.wholeBracket = new eqEd.RightSquareWholeBracket(this.parent, "MathJax_Size4");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else {
            var numberOfMiddleBrackets = Math.ceil((this.heightRatio - 0.6)/0.45) + 1;
            this.topBracket = new eqEd.RightSquareTopBracket(this.parent);
            this.bottomBracket = new eqEd.RightSquareBottomBracket(this.parent);
            this.topBracket.parent = this;
            this.bottomBracket.parent = this;
            this.domObj.append(this.topBracket.domObj);
            this.domObj.append(this.bottomBracket.domObj);
            for (var i = 0; i < numberOfMiddleBrackets; i++) {
                var middleBracket = new eqEd.RightSquareMiddleBracket(this.parent, i);
                middleBracket.parent = this;
                this.domObj.append(middleBracket.domObj);
                this.middleBrackets.push(middleBracket);
            }
            this.children = [this.topBracket].concat(this.middleBrackets).concat([this.bottomBracket]);
        }
    }
})();

/* End eq/js/equation-components/misc/rightSquareBracket.js*/

/* Begin eq/js/equation-components/misc/rightSquareWholeBracket.js*/

eqEd.RightSquareWholeBracket = function(parent, fontStyle) {
    eqEd.WholeBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightSquareWholeBracket";
    
    this.character = "]";
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    this.adjustTop = 0;
    if (this.fontStyle === "MathJax_Main") {
        this.adjustTop = -0.0625;
    } else if (this.fontStyle === "MathJax_Size3") {
        this.adjustTop = 0.7;
    } else if (this.fontStyle === "MathJax_Size4") {
        this.adjustTop = 0.995;
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.RightSquareWholeBracket.prototype = Object.create(eqEd.WholeBracket.prototype);
    eqEd.RightSquareWholeBracket.prototype.constructor = eqEd.RightSquareWholeBracket;
})();

/* End eq/js/equation-components/misc/rightSquareWholeBracket.js*/

/* Begin eq/js/equation-components/misc/rightSquareTopBracket.js*/

eqEd.RightSquareTopBracket = function(parent) {
    eqEd.TopBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightSquareTopBracket";
    
    this.character = "⎤";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();
    this.adjustTop = 0.4;
};
(function() {
    // subclass extends superclass
    eqEd.RightSquareTopBracket.prototype = Object.create(eqEd.TopBracket.prototype);
    eqEd.RightSquareTopBracket.prototype.constructor = eqEd.RightSquareTopBracket;
})();

/* End eq/js/equation-components/misc/rightSquareTopBracket.js*/

/* Begin eq/js/equation-components/misc/rightSquareMiddleBracket.js*/

eqEd.RightSquareMiddleBracket = function(parent, index) {
    eqEd.MiddleBracket.call(this, parent, index); // call super constructor.
    this.className = "eqEd.RightSquareMiddleBracket";
    
    this.character = "⎥";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();
    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            return (0.45 * this.index - 0.15) * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightSquareMiddleBracket.prototype = Object.create(eqEd.MiddleBracket.prototype);
    eqEd.RightSquareMiddleBracket.prototype.constructor = eqEd.RightSquareMiddleBracket;
})();

/* End eq/js/equation-components/misc/rightSquareMiddleBracket.js*/

/* Begin eq/js/equation-components/misc/rightSquareBottomBracket.js*/

eqEd.RightSquareBottomBracket = function(parent) {
    eqEd.BottomBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightSquareBottomBracket";
    
    this.character = "⎦";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            var topVal = this.parent.middleBrackets[this.parent.middleBrackets.length - 1].top - 0.65 * fontHeight;
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightSquareBottomBracket.prototype = Object.create(eqEd.BottomBracket.prototype);
    eqEd.RightSquareBottomBracket.prototype.constructor = eqEd.RightSquareBottomBracket;
})();

/* End eq/js/equation-components/misc/rightSquareBottomBracket.js*/

/* Begin eq/js/equation-components/misc/leftCurlyBracket.js*/

eqEd.LeftCurlyBracket = function(parent) {
	eqEd.LeftBracket.call(this, parent); // call super constructor.
	this.className = "eqEd.LeftCurlyBracket";

    this.matchingBracketCtor = eqEd.RightCurlyBracket;
    this.wholeBracket = new eqEd.LeftCurlyWholeBracket(this.parent, "MathJax_Main");
    this.topBracket = null;
    this.middleBrackets = [];
    this.bottomBracket = null;

    this.wholeBracket.parent = this;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.wholeBracket.domObj);

    this.children = [this.wholeBracket];

	// Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var widthVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                widthVal = 0.511111 * fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                widthVal = 0.755555 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                widthVal = 0.8 * fontHeight;
            } else if (this.heightRatio > 3 && this.heightRatio <= 3.33) {
                widthVal = 0.666666 * fontHeight;
            } else {
                widthVal = 0.888888 * fontHeight;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var heightVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                heightVal = fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                heightVal = 2.4 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                heightVal = 3 * fontHeight;
            } else if (this.heightRatio > 3 && this.heightRatio <= 3.33) {
                heightVal = 3.33 * fontHeight;
            } else {
                var bottomBracketTop = this.bottomBracket.top / fontHeight;
                heightVal = (bottomBracketTop + 1.652778 - this.padTop) * fontHeight;
            }
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftCurlyBracket.prototype = Object.create(eqEd.LeftBracket.prototype);
    eqEd.LeftCurlyBracket.prototype.constructor = eqEd.LeftCurlyBracket;
    eqEd.LeftCurlyBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="bracket leftBracket leftCurlyBracket"></div>')
    };
    // This is a callback that happens after this.heightRation gets calculated.
    eqEd.LeftCurlyBracket.prototype.updateBracketStructure = function() {
        this.domObj.empty();
        this.wholeBracket = null;
        this.topBracket = null;
        this.middleBrackets = [];
        this.bottomBracket = null;
        this.children = [];
        if (this.heightRatio <= 1.5) {
            this.wholeBracket = new eqEd.LeftCurlyWholeBracket(this.parent, "MathJax_Main");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
            this.wholeBracket = new eqEd.LeftCurlyWholeBracket(this.parent, "MathJax_Size3");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
            this.wholeBracket = new eqEd.LeftCurlyWholeBracket(this.parent, "MathJax_Size4");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else {
            var numberOfMiddleBrackets = Math.round((this.heightRatio - 3.4) / 0.231);
            numberOfMiddleBrackets = (numberOfMiddleBrackets % 2 !== 0) ? (numberOfMiddleBrackets + 1) : numberOfMiddleBrackets;
            this.topBracket = new eqEd.LeftCurlyTopBracket(this.parent);
            this.bottomBracket = new eqEd.LeftCurlyBottomBracket(this.parent);
            this.topBracket.parent = this;
            this.bottomBracket.parent = this;
            this.domObj.append(this.topBracket.domObj);
            this.domObj.append(this.bottomBracket.domObj);
            for (var i = 0; i < Math.round(0.5 * numberOfMiddleBrackets); i++) {
                var middleBracket = new eqEd.LeftCurlyMiddleBracket(this.parent, i, "middleVert");
                middleBracket.parent = this;
                this.domObj.append(middleBracket.domObj);
                this.middleBrackets.push(middleBracket);
            }
            var middleCurly = new eqEd.LeftCurlyMiddleBracket(this.parent, Math.round(0.5 * numberOfMiddleBrackets), "middleCurly");
            middleCurly.parent = this;
            this.domObj.append(middleCurly.domObj);
            this.middleBrackets.push(middleCurly);
            for (var i = (Math.round(0.5 * numberOfMiddleBrackets) + 1); i < (numberOfMiddleBrackets + 1); i++) {
                var middleBracket = new eqEd.LeftCurlyMiddleBracket(this.parent, i, "middleVert");
                middleBracket.parent = this;
                this.domObj.append(middleBracket.domObj);
                this.middleBrackets.push(middleBracket);
            }
            this.children = [this.topBracket].concat(this.middleBrackets).concat([this.bottomBracket]);
        }
    }
})();

/* End eq/js/equation-components/misc/leftCurlyBracket.js*/

/* Begin eq/js/equation-components/misc/leftCurlyWholeBracket.js*/

eqEd.LeftCurlyWholeBracket = function(parent, fontStyle) {
    eqEd.WholeBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftCurlyWholeBracket";
    
    this.character = "{";
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    this.adjustTop = 0;
    if (this.fontStyle === "MathJax_Main") {
        this.adjustTop = -0.0625;
    } else if (this.fontStyle === "MathJax_Size3") {
        this.adjustTop = 0.7;
    } else if (this.fontStyle === "MathJax_Size4") {
        this.adjustTop = 0.995;
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.LeftCurlyWholeBracket.prototype = Object.create(eqEd.WholeBracket.prototype);
    eqEd.LeftCurlyWholeBracket.prototype.constructor = eqEd.LeftCurlyWholeBracket;
})();

/* End eq/js/equation-components/misc/leftCurlyWholeBracket.js*/

/* Begin eq/js/equation-components/misc/leftCurlyTopBracket.js*/

eqEd.LeftCurlyTopBracket = function(parent) {
    eqEd.TopBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftCurlyTopBracket";
    
    this.character = "⎧";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();
    this.adjustTop = 0.15;
};
(function() {
    // subclass extends superclass
    eqEd.LeftCurlyTopBracket.prototype = Object.create(eqEd.TopBracket.prototype);
    eqEd.LeftCurlyTopBracket.prototype.constructor = eqEd.LeftCurlyTopBracket;
})();

/* End eq/js/equation-components/misc/leftCurlyTopBracket.js*/

/* Begin eq/js/equation-components/misc/leftCurlyMiddleBracket.js*/

eqEd.LeftCurlyMiddleBracket = function(parent, index, characterType) {
    eqEd.MiddleBracket.call(this, parent, index); // call super constructor.
    this.className = "eqEd.LeftCurlyMiddleBracket";
    
    this.characterType = characterType;
    if (this.characterType === "middleVert") {
        this.character = "⎪";
    } else if (this.characterType === "middleCurly") {
        this.character = "⎨";
    }
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            var topVal = 0;
            var numSegs = this.parent.middleBrackets.length - 1;
            var adjustTopFactor = 0.231;
            if (this.index < Math.round(numSegs / 2)) {
                topVal = ((this.index + 1) * adjustTopFactor + 0.15) * fontHeight;
            } else if (this.index === Math.round(numSegs / 2)) {
                topVal = (this.index * adjustTopFactor + 1.1 + 0.15) * fontHeight;
            } else {
                var centerBracket = Math.round(numSegs / 2) * adjustTopFactor + 1.1 + 0.15;
                topVal = (centerBracket + 0.878 + (this.index - Math.round(numSegs / 2) - 1) * adjustTopFactor) * fontHeight;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftCurlyMiddleBracket.prototype = Object.create(eqEd.MiddleBracket.prototype);
    eqEd.LeftCurlyMiddleBracket.prototype.constructor = eqEd.LeftCurlyMiddleBracket;
})();

/* End eq/js/equation-components/misc/leftCurlyMiddleBracket.js*/

/* Begin eq/js/equation-components/misc/leftCurlyBottomBracket.js*/

eqEd.LeftCurlyBottomBracket = function(parent) {
    eqEd.BottomBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftCurlyBottomBracket";
    
    this.character = "⎩";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            var length = this.parent.middleBrackets.length;
            var centerIndex = Math.floor(length / 2);
            return this.parent.middleBrackets[centerIndex].top + ((length - 1 - centerIndex) * 0.231 + 0.5) * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftCurlyBottomBracket.prototype = Object.create(eqEd.BottomBracket.prototype);
    eqEd.LeftCurlyBottomBracket.prototype.constructor = eqEd.LeftCurlyBottomBracket;
})();

/* End eq/js/equation-components/misc/leftCurlyBottomBracket.js*/

/* Begin eq/js/equation-components/misc/rightCurlyBracket.js*/

eqEd.RightCurlyBracket = function(parent) {
    eqEd.RightBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightCurlyBracket";

    this.matchingBracketCtor = eqEd.LeftCurlyBracket;
    this.wholeBracket = new eqEd.RightCurlyWholeBracket(parent, "MathJax_Main");
    this.topBracket = null;
    this.middleBrackets = [];
    this.bottomBracket = null;

    this.wholeBracket.parent = this;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.wholeBracket.domObj);

    this.children = [this.wholeBracket];

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var widthVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                widthVal = 0.511111 * fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                widthVal = 0.755555 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                widthVal = 0.8 * fontHeight;
            } else if (this.heightRatio > 3 && this.heightRatio <= 3.33) {
                widthVal = 0.666666 * fontHeight;
            } else {
                widthVal = 0.888888 * fontHeight;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var heightVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                heightVal = fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                heightVal = 2.4 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                heightVal = 3 * fontHeight;
            } else if (this.heightRatio > 3 && this.heightRatio <= 3.33) {
                heightVal = 3.33 * fontHeight;
            } else {
                var bottomBracketTop = this.bottomBracket.top / fontHeight;
                heightVal = (bottomBracketTop + 1.652778 - this.padTop) * fontHeight;
            }
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightCurlyBracket.prototype = Object.create(eqEd.RightBracket.prototype);
    eqEd.RightCurlyBracket.prototype.constructor = eqEd.RightCurlyBracket;
    eqEd.RightCurlyBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="bracket rightBracket rightCurlyBracket"></div>')
    };
    // This is a callback that happens after this.heightRation gets calculated.
    eqEd.RightCurlyBracket.prototype.updateBracketStructure = function() {
        this.domObj.empty();
        this.wholeBracket = null;
        this.topBracket = null;
        this.middleBrackets = [];
        this.bottomBracket = null;
        this.children = [];
        if (this.heightRatio <= 1.5) {
            this.wholeBracket = new eqEd.RightCurlyWholeBracket(this.parent, "MathJax_Main");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
            this.wholeBracket = new eqEd.RightCurlyWholeBracket(this.parent, "MathJax_Size3");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
            this.wholeBracket = new eqEd.RightCurlyWholeBracket(this.parent, "MathJax_Size4");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else {
            var numberOfMiddleBrackets = Math.round((this.heightRatio - 3.4) / 0.231);
            numberOfMiddleBrackets = (numberOfMiddleBrackets % 2 !== 0) ? (numberOfMiddleBrackets + 1) : numberOfMiddleBrackets;
            this.topBracket = new eqEd.RightCurlyTopBracket(this.parent);
            this.bottomBracket = new eqEd.RightCurlyBottomBracket(this.parent);
            this.topBracket.parent = this;
            this.bottomBracket.parent = this;
            this.domObj.append(this.topBracket.domObj);
            this.domObj.append(this.bottomBracket.domObj);
            for (var i = 0; i < Math.round(0.5 * numberOfMiddleBrackets); i++) {
                var middleBracket = new eqEd.RightCurlyMiddleBracket(this.parent, i, "middleVert");
                middleBracket.parent = this;
                this.domObj.append(middleBracket.domObj);
                this.middleBrackets.push(middleBracket);
            }
            var middleCurly = new eqEd.RightCurlyMiddleBracket(this.parent, Math.round(0.5 * numberOfMiddleBrackets), "middleCurly");
            middleCurly.parent = this;
            this.domObj.append(middleCurly.domObj);
            this.middleBrackets.push(middleCurly);
            for (var i = (Math.round(0.5 * numberOfMiddleBrackets) + 1); i < (numberOfMiddleBrackets + 1); i++) {
                var middleBracket = new eqEd.RightCurlyMiddleBracket(this.parent, i, "middleVert");
                middleBracket.parent = this;
                this.domObj.append(middleBracket.domObj);
                this.middleBrackets.push(middleBracket);
            }
            this.children = [this.topBracket].concat(this.middleBrackets).concat([this.bottomBracket]);
        }
    }
})();

/* End eq/js/equation-components/misc/rightCurlyBracket.js*/

/* Begin eq/js/equation-components/misc/rightCurlyWholeBracket.js*/

eqEd.RightCurlyWholeBracket = function(parent, fontStyle) {
    eqEd.WholeBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightCurlyWholeBracket";
    
    this.character = "}";
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    this.adjustTop = 0;
    if (this.fontStyle === "MathJax_Main") {
        this.adjustTop = -0.0625;
    } else if (this.fontStyle === "MathJax_Size3") {
        this.adjustTop = 0.7;
    } else if (this.fontStyle === "MathJax_Size4") {
        this.adjustTop = 0.995;
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.RightCurlyWholeBracket.prototype = Object.create(eqEd.WholeBracket.prototype);
    eqEd.RightCurlyWholeBracket.prototype.constructor = eqEd.RightCurlyWholeBracket;
})();

/* End eq/js/equation-components/misc/rightCurlyWholeBracket.js*/

/* Begin eq/js/equation-components/misc/rightCurlyTopBracket.js*/

eqEd.RightCurlyTopBracket = function(parent) {
    eqEd.TopBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightCurlyTopBracket";
    
    this.character = "⎫";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();
    this.adjustTop = 0.15;

};
(function() {
    // subclass extends superclass
    eqEd.RightCurlyTopBracket.prototype = Object.create(eqEd.TopBracket.prototype);
    eqEd.RightCurlyTopBracket.prototype.constructor = eqEd.RightCurlyTopBracket;
})();

/* End eq/js/equation-components/misc/rightCurlyTopBracket.js*/

/* Begin eq/js/equation-components/misc/rightCurlyMiddleBracket.js*/

eqEd.RightCurlyMiddleBracket = function(parent, index, characterType) {
    eqEd.MiddleBracket.call(this, parent, index); // call super constructor.
    this.className = "eqEd.RightCurlyMiddleBracket";
    
    this.characterType = characterType;
    if (this.characterType === "middleVert") {
        this.character = "⎪";
    } else if (this.characterType === "middleCurly") {
        this.character = "⎬";
    }
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            var topVal = 0;
            var numSegs = this.parent.middleBrackets.length - 1;
            var adjustTopFactor = 0.231;
            if (this.index < Math.round(numSegs / 2)) {
                topVal = ((this.index + 1) * adjustTopFactor + 0.15) * fontHeight;
            } else if (this.index === Math.round(numSegs / 2)) {
                topVal = (this.index * adjustTopFactor + 1.1 + 0.15) * fontHeight;
            } else {
                var centerBracket = Math.round(numSegs / 2) * adjustTopFactor + 1.1 + 0.15;
                topVal = (centerBracket + 0.878 + (this.index - Math.round(numSegs / 2) - 1) * adjustTopFactor) * fontHeight;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightCurlyMiddleBracket.prototype = Object.create(eqEd.MiddleBracket.prototype);
    eqEd.RightCurlyMiddleBracket.prototype.constructor = eqEd.RightCurlyMiddleBracket;
})();

/* End eq/js/equation-components/misc/rightCurlyMiddleBracket.js*/

/* Begin eq/js/equation-components/misc/rightCurlyBottomBracket.js*/

eqEd.RightCurlyBottomBracket = function(parent) {
    eqEd.BottomBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightCurlyBottomBracket";
    
    this.character = "⎭";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();
    
    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            var length = this.parent.middleBrackets.length;
            var centerIndex = Math.floor(length / 2);
            return this.parent.middleBrackets[centerIndex].top + ((length - 1 - centerIndex) * 0.231 + 0.5) * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightCurlyBottomBracket.prototype = Object.create(eqEd.BottomBracket.prototype);
    eqEd.RightCurlyBottomBracket.prototype.constructor = eqEd.RightCurlyBottomBracket;
})();

/* End eq/js/equation-components/misc/rightCurlyBottomBracket.js*/

/* Begin eq/js/equation-components/misc/leftAngleBracket.js*/

eqEd.LeftAngleBracket = function(parent) {
	eqEd.LeftBracket.call(this, parent); // call super constructor.
	this.className = "eqEd.LeftAngleBracket";

    this.matchingBracketCtor = eqEd.RightAngleBracket;
    this.wholeBracket = new eqEd.LeftAngleWholeBracket(parent, "MathJax_Main");
    this.topBracket = null;
    this.middleBrackets = [];
    this.bottomBracket = null;

    this.wholeBracket.parent = this;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.wholeBracket.domObj);

    this.children = [this.wholeBracket];

	// Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var widthVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                widthVal = 0.377777 * fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                widthVal = 0.733333 * fontHeight;
            } else {
                widthVal = 0.777777 * fontHeight;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var heightVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                heightVal = fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                heightVal = 2.4 * fontHeight;
            } else {
                heightVal = 3 * fontHeight;
            }
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftAngleBracket.prototype = Object.create(eqEd.LeftBracket.prototype);
    eqEd.LeftAngleBracket.prototype.constructor = eqEd.LeftAngleBracket;
    eqEd.LeftAngleBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class=" bracket leftBracket leftAngleBracket"></div>')
    };
    // This is a callback that happens after this.heightRation gets calculated.
    eqEd.LeftAngleBracket.prototype.updateBracketStructure = function() {
        this.domObj.empty();
        this.wholeBracket = null;
        this.topBracket = null;
        this.middleBrackets = [];
        this.bottomBracket = null;
        this.children = [];
        if (this.heightRatio <= 1.5) {
            this.wholeBracket = new eqEd.LeftAngleWholeBracket(this.parent, "MathJax_Main");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
            this.wholeBracket = new eqEd.LeftAngleWholeBracket(this.parent, "MathJax_Size3");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else {
            this.wholeBracket = new eqEd.LeftAngleWholeBracket(this.parent, "MathJax_Size4");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        }
    }
})();

/* End eq/js/equation-components/misc/leftAngleBracket.js*/

/* Begin eq/js/equation-components/misc/leftAngleWholeBracket.js*/

eqEd.LeftAngleWholeBracket = function(parent, fontStyle) {
    eqEd.WholeBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftAngleWholeBracket";
    
    this.character = "⟨";
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    this.adjustTop = 0;
    if (this.fontStyle === "MathJax_Main") {
        this.adjustTop = -0.0625;
    } else if (this.fontStyle === "MathJax_Size3") {
        this.adjustTop = 0.7;
    } else if (this.fontStyle === "MathJax_Size4") {
        this.adjustTop = 0.995;
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.LeftAngleWholeBracket.prototype = Object.create(eqEd.WholeBracket.prototype);
    eqEd.LeftAngleWholeBracket.prototype.constructor = eqEd.LeftAngleWholeBracket;
})();

/* End eq/js/equation-components/misc/leftAngleWholeBracket.js*/

/* Begin eq/js/equation-components/misc/rightAngleBracket.js*/

eqEd.RightAngleBracket = function(parent) {
	eqEd.RightBracket.call(this, parent); // call super constructor.
	this.className = "eqEd.RightAngleBracket";

    this.matchingBracketCtor = eqEd.LeftAngleBracket;
    this.wholeBracket = new eqEd.RightAngleWholeBracket(parent, "MathJax_Main");
    this.topBracket = null;
    this.middleBrackets = [];
    this.bottomBracket = null;

    this.wholeBracket.parent = this;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.wholeBracket.domObj);

    this.children = [this.wholeBracket];

	// Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var widthVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                widthVal = 0.377777 * fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                widthVal = 0.733333 * fontHeight;
            } else {
                widthVal = 0.777777 * fontHeight;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var heightVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                heightVal = fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                heightVal = 2.4 * fontHeight;
            } else {
                heightVal = 3 * fontHeight;
            }
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightAngleBracket.prototype = Object.create(eqEd.RightBracket.prototype);
    eqEd.RightAngleBracket.prototype.constructor = eqEd.RightAngleBracket;
    eqEd.RightAngleBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="bracket leftBracket RightAngleBracket"></div>')
    };
    // This is a callback that happens after this.heightRation gets calculated.
    eqEd.RightAngleBracket.prototype.updateBracketStructure = function() {
        this.domObj.empty();
        this.wholeBracket = null;
        this.topBracket = null;
        this.middleBrackets = [];
        this.bottomBracket = null;
        this.children = [];
        if (this.heightRatio <= 1.5) {
            this.wholeBracket = new eqEd.RightAngleWholeBracket(this.parent, "MathJax_Main");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
            this.wholeBracket = new eqEd.RightAngleWholeBracket(this.parent, "MathJax_Size3");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else {
            this.wholeBracket = new eqEd.RightAngleWholeBracket(this.parent, "MathJax_Size4");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        }
    }
})();

/* End eq/js/equation-components/misc/rightAngleBracket.js*/

/* Begin eq/js/equation-components/misc/rightAngleWholeBracket.js*/

eqEd.RightAngleWholeBracket = function(parent, fontStyle) {
    eqEd.WholeBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightAngleWholeBracket";
    
    this.character = "⟩";
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    this.adjustTop = 0;
    if (this.fontStyle === "MathJax_Main") {
        this.adjustTop = -0.0625;
    } else if (this.fontStyle === "MathJax_Size3") {
        this.adjustTop = 0.7;
    } else if (this.fontStyle === "MathJax_Size4") {
        this.adjustTop = 0.995;
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.RightAngleWholeBracket.prototype = Object.create(eqEd.WholeBracket.prototype);
    eqEd.RightAngleWholeBracket.prototype.constructor = eqEd.RightAngleWholeBracket;
})();

/* End eq/js/equation-components/misc/rightAngleWholeBracket.js*/

/* Begin eq/js/equation-components/misc/leftFloorBracket.js*/

eqEd.LeftFloorBracket = function(parent) {
    eqEd.LeftBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftFloorBracket";

    this.matchingBracketCtor = eqEd.RightFloorBracket;
    this.wholeBracket = new eqEd.LeftFloorWholeBracket(parent, "MathJax_Main");
    this.topBracket = null;
    this.middleBrackets = [];
    this.bottomBracket = null;

    this.wholeBracket.parent = this;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.wholeBracket.domObj);

    this.children = [this.wholeBracket];

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var widthVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                widthVal = 0.444444 * fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                widthVal = 0.5777777 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                widthVal = 0.644444 * fontHeight;
            } else {
                widthVal = 0.666666 * fontHeight;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var heightVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                heightVal = fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                heightVal = 2.4 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                heightVal = 3 * fontHeight;
            } else {
                heightVal = (0.6 + (0.45 * (this.middleBrackets.length - 1))) * fontHeight;
            }
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftFloorBracket.prototype = Object.create(eqEd.LeftBracket.prototype);
    eqEd.LeftFloorBracket.prototype.constructor = eqEd.LeftFloorBracket;
    eqEd.LeftFloorBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="bracket leftBracket leftFloorBracket"></div>')
    };
    // This is a callback that happens after this.heightRation gets calculated.
    eqEd.LeftFloorBracket.prototype.updateBracketStructure = function() {
        this.domObj.empty();
        this.wholeBracket = null;
        this.topBracket = null;
        this.middleBrackets = [];
        this.bottomBracket = null;
        this.children = [];
        if (this.heightRatio <= 1.5) {
            this.wholeBracket = new eqEd.LeftFloorWholeBracket(this.parent, "MathJax_Main");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
            this.wholeBracket = new eqEd.LeftFloorWholeBracket(this.parent, "MathJax_Size3");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
            this.wholeBracket = new eqEd.LeftFloorWholeBracket(this.parent, "MathJax_Size4");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else {
            var numberOfMiddleBrackets = Math.ceil((this.heightRatio - 0.6)/0.45) + 1;
            this.bottomBracket = new eqEd.LeftFloorBottomBracket(this.parent);
            this.bottomBracket.parent = this;
            this.domObj.append(this.bottomBracket.domObj);
            for (var i = 0; i < numberOfMiddleBrackets; i++) {
                var middleBracket = new eqEd.LeftFloorMiddleBracket(this.parent, i);
                middleBracket.parent = this;
                this.domObj.append(middleBracket.domObj);
                this.middleBrackets.push(middleBracket);
            }
            this.children = (this.middleBrackets).concat([this.bottomBracket]);
        }
    }
})();

/* End eq/js/equation-components/misc/leftFloorBracket.js*/

/* Begin eq/js/equation-components/misc/leftFloorWholeBracket.js*/

eqEd.LeftFloorWholeBracket = function(parent, fontStyle) {
    eqEd.WholeBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftFloorWholeBracket";
    
    this.character = "⌊";
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    this.adjustTop = 0;
    if (this.fontStyle === "MathJax_Main") {
        this.adjustTop = -0.0625;
    } else if (this.fontStyle === "MathJax_Size3") {
        this.adjustTop = 0.7;
    } else if (this.fontStyle === "MathJax_Size4") {
        this.adjustTop = 0.995;
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.LeftFloorWholeBracket.prototype = Object.create(eqEd.WholeBracket.prototype);
    eqEd.LeftFloorWholeBracket.prototype.constructor = eqEd.LeftFloorWholeBracket;
})();

/* End eq/js/equation-components/misc/leftFloorWholeBracket.js*/

/* Begin eq/js/equation-components/misc/leftFloorMiddleBracket.js*/

eqEd.LeftFloorMiddleBracket = function(parent, index) {
    eqEd.MiddleBracket.call(this, parent, index); // call super constructor.
    this.className = "eqEd.LeftFloorMiddleBracket";
    
    this.character = "⎢";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            return (0.45 * this.index - 0.15) * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftFloorMiddleBracket.prototype = Object.create(eqEd.MiddleBracket.prototype);
    eqEd.LeftFloorMiddleBracket.prototype.constructor = eqEd.LeftFloorMiddleBracket;
})();

/* End eq/js/equation-components/misc/leftFloorMiddleBracket.js*/

/* Begin eq/js/equation-components/misc/leftFloorBottomBracket.js*/

eqEd.LeftFloorBottomBracket = function(parent) {
    eqEd.BottomBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftFloorBottomBracket";
    
    this.character = "⎣";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            var topVal = this.parent.middleBrackets[this.parent.middleBrackets.length - 1].top - 0.65 * fontHeight;
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftFloorBottomBracket.prototype = Object.create(eqEd.BottomBracket.prototype);
    eqEd.LeftFloorBottomBracket.prototype.constructor = eqEd.LeftFloorBottomBracket;
})();

/* End eq/js/equation-components/misc/leftFloorBottomBracket.js*/

/* Begin eq/js/equation-components/misc/rightFloorBracket.js*/

eqEd.RightFloorBracket = function(parent) {
    eqEd.RightBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightFloorBracket";

    this.matchingBracketCtor = eqEd.LeftFloorBracket;
    this.wholeBracket = new eqEd.RightFloorWholeBracket(parent, "MathJax_Main");
    this.topBracket = null;
    this.middleBrackets = [];
    this.bottomBracket = null;

    this.wholeBracket.parent = this;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.wholeBracket.domObj);

    this.children = [this.wholeBracket];

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var widthVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                widthVal = 0.444444 * fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                widthVal = 0.577777 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                widthVal = 0.644444 * fontHeight;
            } else {
                widthVal = 0.666666 * fontHeight;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var heightVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                heightVal = fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                heightVal = 2.4 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                heightVal = 3 * fontHeight;
            } else {
                heightVal = (0.6 + (0.45 * (this.middleBrackets.length - 1))) * fontHeight;
            }
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightFloorBracket.prototype = Object.create(eqEd.RightBracket.prototype);
    eqEd.RightFloorBracket.prototype.constructor = eqEd.RightFloorBracket;
    eqEd.RightFloorBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="bracket rightBracket rightFloorBracket"></div>')
    };
    // This is a callback that happens after this.heightRation gets calculated.
    eqEd.RightFloorBracket.prototype.updateBracketStructure = function() {
        this.domObj.empty();
        this.wholeBracket = null;
        this.topBracket = null;
        this.middleBrackets = [];
        this.bottomBracket = null;
        this.children = [];
        if (this.heightRatio <= 1.5) {
            this.wholeBracket = new eqEd.RightFloorWholeBracket(this.parent, "MathJax_Main");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
            this.wholeBracket = new eqEd.RightFloorWholeBracket(this.parent, "MathJax_Size3");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
            this.wholeBracket = new eqEd.RightFloorWholeBracket(this.parent, "MathJax_Size4");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else {
            var numberOfMiddleBrackets = Math.ceil((this.heightRatio - 0.6)/0.45) + 1;
            this.bottomBracket = new eqEd.RightFloorBottomBracket(this.parent);
            this.bottomBracket.parent = this;
            this.domObj.append(this.bottomBracket.domObj);
            for (var i = 0; i < numberOfMiddleBrackets; i++) {
                var middleBracket = new eqEd.RightFloorMiddleBracket(this.parent, i);
                middleBracket.parent = this;
                this.domObj.append(middleBracket.domObj);
                this.middleBrackets.push(middleBracket);
            }
            this.children = (this.middleBrackets).concat([this.bottomBracket]);
        }
    }
})();

/* End eq/js/equation-components/misc/rightFloorBracket.js*/

/* Begin eq/js/equation-components/misc/rightFloorWholeBracket.js*/

eqEd.RightFloorWholeBracket = function(parent, fontStyle) {
    eqEd.WholeBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightFloorWholeBracket";
    
    this.character = "⌋";
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    this.adjustTop = 0;
    if (this.fontStyle === "MathJax_Main") {
        this.adjustTop = -0.0625;
    } else if (this.fontStyle === "MathJax_Size3") {
        this.adjustTop = 0.7;
    } else if (this.fontStyle === "MathJax_Size4") {
        this.adjustTop = 0.995;
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.RightFloorWholeBracket.prototype = Object.create(eqEd.WholeBracket.prototype);
    eqEd.RightFloorWholeBracket.prototype.constructor = eqEd.RightFloorWholeBracket;
})();

/* End eq/js/equation-components/misc/rightFloorWholeBracket.js*/

/* Begin eq/js/equation-components/misc/rightFloorMiddleBracket.js*/

eqEd.RightFloorMiddleBracket = function(parent, index) {
    eqEd.MiddleBracket.call(this, parent, index); // call super constructor.
    this.className = "eqEd.RightFloorMiddleBracket";
    
    this.character = "⎥";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            return (0.45 * this.index - 0.15) * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightFloorMiddleBracket.prototype = Object.create(eqEd.MiddleBracket.prototype);
    eqEd.RightFloorMiddleBracket.prototype.constructor = eqEd.RightFloorMiddleBracket;
})();

/* End eq/js/equation-components/misc/rightFloorMiddleBracket.js*/

/* Begin eq/js/equation-components/misc/rightFloorBottomBracket.js*/

eqEd.RightFloorBottomBracket = function(parent) {
    eqEd.BottomBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightFloorBottomBracket";
    
    this.character = "⎦";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            var topVal = this.parent.middleBrackets[this.parent.middleBrackets.length - 1].top - 0.65 * fontHeight;
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightFloorBottomBracket.prototype = Object.create(eqEd.BottomBracket.prototype);
    eqEd.RightFloorBottomBracket.prototype.constructor = eqEd.RightFloorBottomBracket;
})();

/* End eq/js/equation-components/misc/rightFloorBottomBracket.js*/

/* Begin eq/js/equation-components/misc/leftCeilBracket.js*/

eqEd.LeftCeilBracket = function(parent) {
	eqEd.LeftBracket.call(this, parent); // call super constructor.
	this.className = "eqEd.LeftCeilBracket";

    this.matchingBracketCtor = eqEd.RightCeilBracket;
    this.wholeBracket = new eqEd.LeftCeilWholeBracket(parent, "MathJax_Main");
    this.topBracket = null;
    this.middleBrackets = [];
    this.bottomBracket = null;

    this.wholeBracket.parent = this;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.wholeBracket.domObj);

    this.children = [this.wholeBracket];

	// Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var widthVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                widthVal = 0.444444 * fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                widthVal = 0.5777777 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                widthVal = 0.644444 * fontHeight;
            } else {
                widthVal = 0.666666 * fontHeight;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var heightVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                heightVal = fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                heightVal = 2.4 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                heightVal = 3 * fontHeight;
            } else {
                heightVal = (0.6 + (0.45 * (this.middleBrackets.length - 1))) * fontHeight;
            }
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftCeilBracket.prototype = Object.create(eqEd.LeftBracket.prototype);
    eqEd.LeftCeilBracket.prototype.constructor = eqEd.LeftCeilBracket;
    eqEd.LeftCeilBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="bracket leftBracket leftCeilBracket"></div>')
    };
    // This is a callback that happens after this.heightRation gets calculated.
    eqEd.LeftCeilBracket.prototype.updateBracketStructure = function() {
        this.domObj.empty();
        this.wholeBracket = null;
        this.topBracket = null;
        this.middleBrackets = [];
        this.bottomBracket = null;
        this.children = [];
        if (this.heightRatio <= 1.5) {
            this.wholeBracket = new eqEd.LeftCeilWholeBracket(this.parent, "MathJax_Main");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
            this.wholeBracket = new eqEd.LeftCeilWholeBracket(this.parent, "MathJax_Size3");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
            this.wholeBracket = new eqEd.LeftCeilWholeBracket(this.parent, "MathJax_Size4");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else {
            var numberOfMiddleBrackets = Math.ceil((this.heightRatio - 0.6)/0.45) + 1;
            this.topBracket = new eqEd.LeftCeilTopBracket(this.parent);
            this.topBracket.parent = this;
            this.domObj.append(this.topBracket.domObj);
            for (var i = 0; i < numberOfMiddleBrackets; i++) {
                var middleBracket = new eqEd.LeftCeilMiddleBracket(this.parent, i);
                middleBracket.parent = this;
                this.domObj.append(middleBracket.domObj);
                this.middleBrackets.push(middleBracket);
            }
            this.children = [this.topBracket].concat(this.middleBrackets);
        }
    }
})();

/* End eq/js/equation-components/misc/leftCeilBracket.js*/

/* Begin eq/js/equation-components/misc/leftCeilWholeBracket.js*/

eqEd.LeftCeilWholeBracket = function(parent, fontStyle) {
    eqEd.WholeBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftCeilWholeBracket";
    
    this.character = "⌈";
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    this.adjustTop = 0;
    if (this.fontStyle === "MathJax_Main") {
        this.adjustTop = -0.0625;
    } else if (this.fontStyle === "MathJax_Size3") {
        this.adjustTop = 0.7;
    } else if (this.fontStyle === "MathJax_Size4") {
        this.adjustTop = 0.995;
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.LeftCeilWholeBracket.prototype = Object.create(eqEd.WholeBracket.prototype);
    eqEd.LeftCeilWholeBracket.prototype.constructor = eqEd.LeftCeilWholeBracket;
})();

/* End eq/js/equation-components/misc/leftCeilWholeBracket.js*/

/* Begin eq/js/equation-components/misc/leftCeilTopBracket.js*/

eqEd.LeftCeilTopBracket = function(parent) {
    eqEd.TopBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftCeilTopBracket";
    
    this.character = "⎡";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();
    this.adjustTop = 0.4;
};
(function() {
    // subclass extends superclass
    eqEd.LeftCeilTopBracket.prototype = Object.create(eqEd.TopBracket.prototype);
    eqEd.LeftCeilTopBracket.prototype.constructor = eqEd.LeftCeilTopBracket;
})();

/* End eq/js/equation-components/misc/leftCeilTopBracket.js*/

/* Begin eq/js/equation-components/misc/leftCeilMiddleBracket.js*/

eqEd.LeftCeilMiddleBracket = function(parent, index) {
    eqEd.MiddleBracket.call(this, parent, index); // call super constructor.
    this.className = "eqEd.LeftCeilMiddleBracket";
    
    this.character = "⎢";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            return (0.45 * this.index - 0.15) * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftCeilMiddleBracket.prototype = Object.create(eqEd.MiddleBracket.prototype);
    eqEd.LeftCeilMiddleBracket.prototype.constructor = eqEd.LeftCeilMiddleBracket;
})();

/* End eq/js/equation-components/misc/leftCeilMiddleBracket.js*/

/* Begin eq/js/equation-components/misc/rightCeilBracket.js*/

eqEd.RightCeilBracket = function(parent) {
    eqEd.RightBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightCeilBracket";

    this.matchingBracketCtor = eqEd.LeftCeilBracket;
    this.wholeBracket = new eqEd.RightCeilWholeBracket(parent, "MathJax_Main");
    this.topBracket = null;
    this.middleBrackets = [];
    this.bottomBracket = null;

    this.wholeBracket.parent = this;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.wholeBracket.domObj);

    this.children = [this.wholeBracket];

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var widthVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                widthVal = 0.444444 * fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                widthVal = 0.5777777 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                widthVal = 0.644444 * fontHeight;
            } else {
                widthVal = 0.666666 * fontHeight;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var heightVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            if (this.heightRatio <= 1.5) {
                heightVal = fontHeight;
            } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
                heightVal = 2.4 * fontHeight;
            } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
                heightVal = 3 * fontHeight;
            } else {
                heightVal = (0.6 + (0.45 * (this.middleBrackets.length - 1))) * fontHeight;
            }
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightCeilBracket.prototype = Object.create(eqEd.RightBracket.prototype);
    eqEd.RightCeilBracket.prototype.constructor = eqEd.RightCeilBracket;
    eqEd.RightCeilBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="bracket rightBracket rightCeilBracket"></div>')
    };
    // This is a callback that happens after this.heightRation gets calculated.
    eqEd.RightCeilBracket.prototype.updateBracketStructure = function() {
        this.domObj.empty();
        this.wholeBracket = null;
        this.topBracket = null;
        this.middleBrackets = [];
        this.bottomBracket = null;
        this.children = [];
        if (this.heightRatio <= 1.5) {
            this.wholeBracket = new eqEd.RightCeilWholeBracket(this.parent, "MathJax_Main");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 1.5 && this.heightRatio <= 2.4) {
            this.wholeBracket = new eqEd.RightCeilWholeBracket(this.parent, "MathJax_Size3");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else if (this.heightRatio > 2.4 && this.heightRatio <= 3) {
            this.wholeBracket = new eqEd.RightCeilWholeBracket(this.parent, "MathJax_Size4");
            this.wholeBracket.parent = this;
            this.domObj.append(this.wholeBracket.domObj);
            this.children = [this.wholeBracket];
        } else {
            var numberOfMiddleBrackets = Math.ceil((this.heightRatio - 0.6)/0.45) + 1;
            this.topBracket = new eqEd.RightCeilTopBracket(this.parent);
            this.topBracket.parent = this;
            this.domObj.append(this.topBracket.domObj);
            for (var i = 0; i < numberOfMiddleBrackets; i++) {
                var middleBracket = new eqEd.RightCeilMiddleBracket(this.parent, i);
                middleBracket.parent = this;
                this.domObj.append(middleBracket.domObj);
                this.middleBrackets.push(middleBracket);
            }
            this.children = [this.topBracket].concat(this.middleBrackets);
        }
    }
})();

/* End eq/js/equation-components/misc/rightCeilBracket.js*/

/* Begin eq/js/equation-components/misc/rightCeilWholeBracket.js*/

eqEd.RightCeilWholeBracket = function(parent, fontStyle) {
    eqEd.WholeBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightCeilWholeBracket";
    
    this.character = "⌉";
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    if (this.fontStyle === "MathJax_Main") {
        this.adjustTop = -0.0625;
    } else if (this.fontStyle === "MathJax_Size3") {
        this.adjustTop = 0.7;
    } else if (this.fontStyle === "MathJax_Size4") {
        this.adjustTop = 0.995;
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.RightCeilWholeBracket.prototype = Object.create(eqEd.WholeBracket.prototype);
    eqEd.RightCeilWholeBracket.prototype.constructor = eqEd.RightCeilWholeBracket;
})();

/* End eq/js/equation-components/misc/rightCeilWholeBracket.js*/

/* Begin eq/js/equation-components/misc/rightCeilTopBracket.js*/

eqEd.RightCeilTopBracket = function(parent) {
    eqEd.TopBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightCeilTopBracket";
    
    this.character = "⎤";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();
    this.adjustTop = 0.4;
};
(function() {
    // subclass extends superclass
    eqEd.RightCeilTopBracket.prototype = Object.create(eqEd.TopBracket.prototype);
    eqEd.RightCeilTopBracket.prototype.constructor = eqEd.RightCeilTopBracket;
})();

/* End eq/js/equation-components/misc/rightCeilTopBracket.js*/

/* Begin eq/js/equation-components/misc/rightCeilMiddleBracket.js*/

eqEd.RightCeilMiddleBracket = function(parent, index) {
    eqEd.MiddleBracket.call(this, parent, index); // call super constructor.
    this.className = "eqEd.RightCeilMiddleBracket";
    
    this.character = "⎥";
    this.fontStyle = "MathJax_Size4";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            return (0.45 * this.index - 0.15) * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightCeilMiddleBracket.prototype = Object.create(eqEd.MiddleBracket.prototype);
    eqEd.RightCeilMiddleBracket.prototype.constructor = eqEd.RightCeilMiddleBracket;
})();

/* End eq/js/equation-components/misc/rightCeilMiddleBracket.js*/

/* Begin eq/js/equation-components/misc/leftAbsValBracket.js*/

eqEd.LeftAbsValBracket = function(parent) {
    eqEd.LeftBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftAbsValBracket";

    this.matchingBracketCtor = eqEd.RightAbsValBracket;
    this.wholeBracket = new eqEd.LeftAbsValWholeBracket(parent, "MathJax_Main");
    this.topBracket = null;
    this.middleBrackets = [];
    this.bottomBracket = null;

    this.wholeBracket.parent = this;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.wholeBracket.domObj);

    this.children = [this.wholeBracket];

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var widthVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            widthVal = 0.2666666 * fontHeight;
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var heightVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var numBrackets = Math.ceil((this.heightRatio - 1.07)/0.5) + 1;
            heightVal = (1.07 + (0.5 * (numBrackets - 1))) * fontHeight;
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftAbsValBracket.prototype = Object.create(eqEd.LeftBracket.prototype);
    eqEd.LeftAbsValBracket.prototype.constructor = eqEd.LeftAbsValBracket;
    eqEd.LeftAbsValBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="bracket leftBracket leftAbsValBracket"></div>')
    };
    // This is a callback that happens after this.heightRation gets calculated.
    eqEd.LeftAbsValBracket.prototype.updateBracketStructure = function() {
        this.domObj.empty();
        this.wholeBracket = null;
        this.topBracket = null;
        this.middleBrackets = [];
        this.bottomBracket = null;
        this.children = [];
        var numberOfMiddleBrackets = Math.ceil((this.heightRatio - 1.07)/0.5) + 1;
        for (var i = 0; i < numberOfMiddleBrackets; i++) {
            var middleBracket = new eqEd.LeftAbsValMiddleBracket(this.parent, i);
            middleBracket.parent = this;
            this.domObj.append(middleBracket.domObj);
            this.middleBrackets.push(middleBracket);
        }
        this.children = this.middleBrackets;
    }
})();

/* End eq/js/equation-components/misc/leftAbsValBracket.js*/

/* Begin eq/js/equation-components/misc/rightAbsValBracket.js*/

eqEd.RightAbsValBracket = function(parent) {
    eqEd.RightBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightAbsValBracket";

    this.matchingBracketCtor = eqEd.LeftAbsValBracket;
    this.wholeBracket = new eqEd.RightAbsValWholeBracket(this.parent, "MathJax_Main");
    this.topBracket = null;
    this.middleBrackets = [];
    this.bottomBracket = null;

    this.wholeBracket.parent = this;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.wholeBracket.domObj);

    this.children = [this.wholeBracket];

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var widthVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            widthVal = 0.266666 * fontHeight;
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var heightVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var numBrackets = Math.ceil((this.heightRatio - 1.07)/0.5) + 1;
            heightVal = (1.07 + (0.5 * (numBrackets - 1))) * fontHeight;
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightAbsValBracket.prototype = Object.create(eqEd.RightBracket.prototype);
    eqEd.RightAbsValBracket.prototype.constructor = eqEd.RightAbsValBracket;
    eqEd.RightAbsValBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="bracket rightBracket rightAbsValBracket"></div>')
    };
    // This is a callback that happens after this.heightRation gets calculated.
    eqEd.RightAbsValBracket.prototype.updateBracketStructure = function() {
        this.domObj.empty();
        this.wholeBracket = null;
        this.topBracket = null;
        this.middleBrackets = [];
        this.bottomBracket = null;
        this.children = [];
        var numberOfMiddleBrackets = Math.ceil((this.heightRatio - 1.07)/0.5) + 1;
        for (var i = 0; i < numberOfMiddleBrackets; i++) {
            var middleBracket = new eqEd.RightAbsValMiddleBracket(this.parent, i);
            middleBracket.parent = this;
            this.domObj.append(middleBracket.domObj);
            this.middleBrackets.push(middleBracket);
        }
        this.children = this.middleBrackets;
    }
})();

/* End eq/js/equation-components/misc/rightAbsValBracket.js*/

/* Begin eq/js/equation-components/misc/leftAbsValWholeBracket.js*/

eqEd.LeftAbsValWholeBracket = function(parent, fontStyle) {
    eqEd.WholeBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftAbsValWholeBracket";
    
    this.character = "|";
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    this.adjustTop = 0;
    if (this.fontStyle === "MathJax_Main") {
        this.adjustTop = -0.0625;
    } else if (this.fontStyle === "MathJax_Size3") {
        this.adjustTop = 0.7;
    } else if (this.fontStyle === "MathJax_Size4") {
        this.adjustTop = 0.995;
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.LeftAbsValWholeBracket.prototype = Object.create(eqEd.WholeBracket.prototype);
    eqEd.LeftAbsValWholeBracket.prototype.constructor = eqEd.LeftAbsValWholeBracket;
})();

/* End eq/js/equation-components/misc/leftAbsValWholeBracket.js*/

/* Begin eq/js/equation-components/misc/leftAbsValMiddleBracket.js*/

eqEd.LeftAbsValMiddleBracket = function(parent, index) {
    eqEd.MiddleBracket.call(this, parent, index); // call super constructor.
    this.className = "eqEd.LeftAbsValMiddleBracket";
    
    this.character = "|";
    this.fontStyle = "MathJax_Main";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            return (0.5 * this.index - 0.06) * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftAbsValMiddleBracket.prototype = Object.create(eqEd.MiddleBracket.prototype);
    eqEd.LeftAbsValMiddleBracket.prototype.constructor = eqEd.LeftAbsValMiddleBracket;
})();

/* End eq/js/equation-components/misc/leftAbsValMiddleBracket.js*/

/* Begin eq/js/equation-components/misc/rightAbsValWholeBracket.js*/

eqEd.RightAbsValWholeBracket = function(parent, fontStyle) {
    eqEd.WholeBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightAbsValWholeBracket";
    
    this.character = "|";
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    this.adjustTop = 0;
    if (this.fontStyle === "MathJax_Main") {
        this.adjustTop = -0.0625;
    } else if (this.fontStyle === "MathJax_Size3") {
        this.adjustTop = 0.7;
    } else if (this.fontStyle === "MathJax_Size4") {
        this.adjustTop = 0.995;
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.RightAbsValWholeBracket.prototype = Object.create(eqEd.WholeBracket.prototype);
    eqEd.RightAbsValWholeBracket.prototype.constructor = eqEd.RightAbsValWholeBracket;
})();

/* End eq/js/equation-components/misc/rightAbsValWholeBracket.js*/

/* Begin eq/js/equation-components/misc/rightAbsValMiddleBracket.js*/

eqEd.RightAbsValMiddleBracket = function(parent, index) {
    eqEd.MiddleBracket.call(this, parent, index); // call super constructor.
    this.className = "eqEd.RightAbsValMiddleBracket";
    
    this.character = "|";
    this.fontStyle = "MathJax_Main";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            return (0.5 * this.index - 0.06) * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightAbsValMiddleBracket.prototype = Object.create(eqEd.MiddleBracket.prototype);
    eqEd.RightAbsValMiddleBracket.prototype.constructor = eqEd.RightAbsValMiddleBracket;
})();

/* End eq/js/equation-components/misc/rightAbsValMiddleBracket.js*/

/* Begin eq/js/equation-components/misc/leftNormBracket.js*/

eqEd.LeftNormBracket = function(parent) {
    eqEd.LeftBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftNormBracket";

    this.matchingBracketCtor = eqEd.RightNormBracket;
    this.wholeBracket = new eqEd.LeftNormWholeBracket(parent, "MathJax_Main");
    this.topBracket = null;
    this.middleBrackets = [];
    this.bottomBracket = null;

    this.wholeBracket.parent = this;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.wholeBracket.domObj);

    this.children = [this.wholeBracket];

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var widthVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            widthVal = 0.4888888 * fontHeight;
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var heightVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var numBrackets = Math.ceil((this.heightRatio - 1.07)/0.5) + 1;
            heightVal = (1.07 + (0.5 * (numBrackets - 1))) * fontHeight;
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftNormBracket.prototype = Object.create(eqEd.LeftBracket.prototype);
    eqEd.LeftNormBracket.prototype.constructor = eqEd.LeftNormBracket;
    eqEd.LeftNormBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="bracket leftBracket leftNormBracket"></div>')
    };
    // This is a callback that happens after this.heightRation gets calculated.
    eqEd.LeftNormBracket.prototype.updateBracketStructure = function() {
        this.domObj.empty();
        this.wholeBracket = null;
        this.topBracket = null;
        this.middleBrackets = [];
        this.bottomBracket = null;
        this.children = [];
        var numberOfMiddleBrackets = Math.ceil((this.heightRatio - 1.07)/0.5) + 1;
        for (var i = 0; i < numberOfMiddleBrackets; i++) {
            var middleBracket = new eqEd.LeftNormMiddleBracket(this.parent, i);
            middleBracket.parent = this;
            this.domObj.append(middleBracket.domObj);
            this.middleBrackets.push(middleBracket);
        }
        this.children = this.middleBrackets;
    }
})();

/* End eq/js/equation-components/misc/leftNormBracket.js*/

/* Begin eq/js/equation-components/misc/rightNormBracket.js*/

eqEd.RightNormBracket = function(parent) {
	eqEd.RightBracket.call(this, parent); // call super constructor.
	this.className = "eqEd.RightNormBracket";

    this.matchingBracketCtor = eqEd.LeftNormBracket;
    this.wholeBracket = new eqEd.RightNormWholeBracket(parent, "MathJax_Main");
    this.topBracket = null;
    this.middleBrackets = [];
    this.bottomBracket = null;

    this.wholeBracket.parent = this;

    this.domObj = this.buildDomObj();
    this.domObj.append(this.wholeBracket.domObj);

    this.children = [this.wholeBracket];

	// Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var widthVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            widthVal = 0.4888888 * fontHeight;
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var heightVal = 0;
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var numBrackets = Math.ceil((this.heightRatio - 1.07)/0.5) + 1;
            heightVal = (1.07 + (0.5 * (numBrackets - 1))) * fontHeight;
            return heightVal;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightNormBracket.prototype = Object.create(eqEd.RightBracket.prototype);
    eqEd.RightNormBracket.prototype.constructor = eqEd.RightNormBracket;
    eqEd.RightNormBracket.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="bracket rightBracket rightNormBracket"></div>')
    };
    // This is a callback that happens after this.heightRation gets calculated.
    eqEd.RightNormBracket.prototype.updateBracketStructure = function() {
        this.domObj.empty();
        this.wholeBracket = null;
        this.topBracket = null;
        this.middleBrackets = [];
        this.bottomBracket = null;
        this.children = [];
        var numberOfMiddleBrackets = Math.ceil((this.heightRatio - 1.07)/0.5) + 1;
        for (var i = 0; i < numberOfMiddleBrackets; i++) {
            var middleBracket = new eqEd.RightNormMiddleBracket(this.parent, i);
            middleBracket.parent = this;
            this.domObj.append(middleBracket.domObj);
            this.middleBrackets.push(middleBracket);
        }
        this.children = this.middleBrackets;
    }
})();

/* End eq/js/equation-components/misc/rightNormBracket.js*/

/* Begin eq/js/equation-components/misc/leftNormWholeBracket.js*/

eqEd.LeftNormWholeBracket = function(parent, fontStyle) {
    eqEd.WholeBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.LeftNormWholeBracket";
    
    this.character = "∥";
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    this.adjustTop = 0;
    if (this.fontStyle === "MathJax_Main") {
        this.adjustTop = -0.0625;
    } else if (this.fontStyle === "MathJax_Size3") {
        this.adjustTop = 0.7;
    } else if (this.fontStyle === "MathJax_Size4") {
        this.adjustTop = 0.995;
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.LeftNormWholeBracket.prototype = Object.create(eqEd.WholeBracket.prototype);
    eqEd.LeftNormWholeBracket.prototype.constructor = eqEd.LeftNormWholeBracket;
})();

/* End eq/js/equation-components/misc/leftNormWholeBracket.js*/

/* Begin eq/js/equation-components/misc/leftNormMiddleBracket.js*/

eqEd.LeftNormMiddleBracket = function(parent, index) {
    eqEd.MiddleBracket.call(this, parent, index); // call super constructor.
    this.className = "eqEd.LeftNormMiddleBracket";
    
    this.character = "∥";
    this.fontStyle = "MathJax_Main";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            return (0.5 * this.index - 0.06) * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LeftNormMiddleBracket.prototype = Object.create(eqEd.MiddleBracket.prototype);
    eqEd.LeftNormMiddleBracket.prototype.constructor = eqEd.LeftNormMiddleBracket;
})();

/* End eq/js/equation-components/misc/leftNormMiddleBracket.js*/

/* Begin eq/js/equation-components/misc/rightNormWholeBracket.js*/

eqEd.RightNormWholeBracket = function(parent, fontStyle) {
    eqEd.WholeBracket.call(this, parent); // call super constructor.
    this.className = "eqEd.RightNormWholeBracket";
    
    this.character = "∥";
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    this.adjustTop = 0;
    if (this.fontStyle === "MathJax_Main") {
        this.adjustTop = -0.0625;
    } else if (this.fontStyle === "MathJax_Size3") {
        this.adjustTop = 0.7;
    } else if (this.fontStyle === "MathJax_Size4") {
        this.adjustTop = 0.995;
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.RightNormWholeBracket.prototype = Object.create(eqEd.WholeBracket.prototype);
    eqEd.RightNormWholeBracket.prototype.constructor = eqEd.RightNormWholeBracket;
})();

/* End eq/js/equation-components/misc/rightNormWholeBracket.js*/

/* Begin eq/js/equation-components/misc/rightNormMiddleBracket.js*/

eqEd.RightNormMiddleBracket = function(parent, index) {
    eqEd.MiddleBracket.call(this, parent, index); // call super constructor.
    this.className = "eqEd.RightNormMiddleBracket";
    
    this.character = "∥";
    this.fontStyle = "MathJax_Main";
    this.domObj = this.buildDomObj();

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.parent.fontSize];
            return (0.5 * this.index - 0.06) * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.RightNormMiddleBracket.prototype = Object.create(eqEd.MiddleBracket.prototype);
    eqEd.RightNormMiddleBracket.prototype.constructor = eqEd.RightNormMiddleBracket;
})();

/* End eq/js/equation-components/misc/rightNormMiddleBracket.js*/

/* Begin eq/js/equation-components/wrappers/bracketPairWrapper.js*/

eqEd.BracketPairWrapper = function(equation, bracketType) {
    eqEd.Wrapper.call(this, equation); // call super constructor.
    this.className = "eqEd.BracketPairWrapper";

    this.bracketType = bracketType;
    var bracketCtors = {
        "parenthesisBracket": {
            'left': eqEd.LeftParenthesisBracket,
            'right': eqEd.RightParenthesisBracket
        },
        "squareBracket": {
            'left': eqEd.LeftSquareBracket,
            'right': eqEd.RightSquareBracket
        },
        "curlyBracket": {
            'left': eqEd.LeftCurlyBracket,
            'right': eqEd.RightCurlyBracket
        },
        "angleBracket": {
            'left': eqEd.LeftAngleBracket,
            'right': eqEd.RightAngleBracket
        },
        "floorBracket": {
            'left': eqEd.LeftFloorBracket,
            'right': eqEd.RightFloorBracket
        },
        "ceilBracket": {
            'left': eqEd.LeftCeilBracket,
            'right': eqEd.RightCeilBracket
        },
        "absValBracket": {
            'left': eqEd.LeftAbsValBracket,
            'right': eqEd.RightAbsValBracket
        },
        "normBracket": {
            'left': eqEd.LeftNormBracket,
            'right': eqEd.RightNormBracket
        }
    };

    this.leftBracket = new bracketCtors[bracketType]['left'](this);
    this.bracketContainer = new eqEd.BracketContainer(this);
    this.rightBracket = new bracketCtors[bracketType]['right'](this);
    this.domObj = this.buildDomObj();
    this.domObj.append(this.leftBracket.domObj);
    this.domObj.append(this.bracketContainer.domObj);
    this.domObj.append(this.rightBracket.domObj);
    
    this.childContainers = [this.bracketContainer];
    this.childNoncontainers = [this.leftBracket, this.rightBracket];

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            return this.leftBracket.width + this.bracketContainer.width + this.rightBracket.width;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
            var topAlignVal = 0;
            if (this.bracketContainer.wrappers.length > 0) {
                var containerTopAlign = this.bracketContainer.wrappers[this.bracketContainer.maxTopAlignIndex].topAlign;
                var bracketTopAlign = 0.5 * this.leftBracket.height;
                if (bracketTopAlign < containerTopAlign) {
                    topAlignVal = containerTopAlign;
                } else {
                    topAlignVal = bracketTopAlign;
                }
            }
            return topAlignVal;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
            var bottomAlignVal = 0;
            if (this.bracketContainer.wrappers.length > 0) {
                var containerBottomAlign = this.bracketContainer.wrappers[this.bracketContainer.maxBottomAlignIndex].bottomAlign;
                var bracketBottomAlign = 0.5 * this.leftBracket.height;
                if (bracketBottomAlign < containerBottomAlign) {
                    bottomAlignVal = containerBottomAlign;
                } else {
                    bottomAlignVal = bracketBottomAlign;
                }
            }
            return bottomAlignVal;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.BracketPairWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.BracketPairWrapper.prototype.constructor = eqEd.BracketPairWrapper;
    eqEd.BracketPairWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdwrapper bracketPairWrapper ' + this.bracketType + '"></div>')
    };
    eqEd.BracketPairWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation, this.bracketType);

        copy.leftBracket = this.leftBracket.clone();
        copy.bracketContainer = this.bracketContainer.clone();
        copy.rightBracket = this.rightBracket.clone();
        copy.leftBracket.parent = copy;
        copy.bracketContainer.parent = copy;
        copy.rightBracket.parent = copy;
        copy.domObj = copy.buildDomObj();
        copy.domObj.append(copy.leftBracket.domObj);
        copy.domObj.append(copy.bracketContainer.domObj);
        copy.domObj.append(copy.rightBracket.domObj);
        
        copy.childNoncontainers = [copy.leftBracket, copy.rightBracket];
        copy.childContainers = [copy.bracketContainer];

        return copy;
    };
    eqEd.BracketPairWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: this.bracketType,
            operands: {
                bracketedExpression: this.bracketContainer.buildJsonObj()
            }
        };
        return jsonObj;
    };

    eqEd.BracketPairWrapper.constructFromJsonObj = function(jsonObj, equation) {
      var bracketPairWrapper = new eqEd.BracketPairWrapper(equation, jsonObj.value);
      for (var i = 0; i < jsonObj.operands.bracketedExpression.length; i++) {
        var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.bracketedExpression[i].type);
        var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.bracketedExpression[i], equation);
        bracketPairWrapper.bracketContainer.addWrappers([i, innerWrapper]);
      }
      return bracketPairWrapper;
    }
})();

/* End eq/js/equation-components/wrappers/bracketPairWrapper.js*/

/* Begin eq/js/equation-components/containers/bracketContainer.js*/

eqEd.BracketContainer = function(parent) {
    eqEd.Container.call(this, parent);
    this.className = "eqEd.BracketContainer";

    this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            return this.parent.leftBracket.width;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var topVal = 0;
            if (this.wrappers.length > 0) {
                /*
                var maxTopAlign = this.wrappers[this.maxTopAlignIndex].topAlign;
                topVal = 0.5 * this.parent.leftBracket.height - maxTopAlign;
                */
                var containerTopAlign = this.wrappers[this.maxTopAlignIndex].topAlign;
                var bracketTopAlign = 0.5 * this.parent.leftBracket.height;
                if (bracketTopAlign > containerTopAlign) {
                    topVal = bracketTopAlign - containerTopAlign;
                }
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var fontSizeVal = "";
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            fontSizeVal = actualParentContainer.fontSize;
            return fontSizeVal;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};

(function() {
    // subclass extends superclass
    eqEd.BracketContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.BracketContainer.prototype.constructor = eqEd.BracketContainer;
    eqEd.BracketContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer bracketContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/bracketContainer.js*/

/* Begin eq/js/equation-components/wrappers/bigOperatorWrapper.js*/

eqEd.BigOperatorWrapper = function(equation, isInline, hasUpperLimit, hasLowerLimit, bigOperatorType) {
    eqEd.Wrapper.call(this, equation); // call super constructor.
    this.className = "eqEd.BigOperatorWrapper";

    this.isInline = isInline;
    this.hasUpperLimit = hasUpperLimit;
    this.hasLowerLimit = hasLowerLimit;
    this.bigOperatorType = bigOperatorType;

    this.upperLimitGap = 0.1;
    this.lowerLimitGap = 0.2;

    this.inlineUpperLimitOverlap = 0.4;
    this.inlineLowerLimitOverlap = 0.4;
    this.inlineLimitGap = 0.1;

    this.bigOperatorSymbolCtors = {
        'sum': eqEd.SumBigOperatorSymbol,
        'bigCap': eqEd.BigCapBigOperatorSymbol,
        'bigCup': eqEd.BigCupBigOperatorSymbol,
        'bigSqCap': eqEd.BigSqCapBigOperatorSymbol,
        'bigSqCup': eqEd.BigSqCupBigOperatorSymbol,
        'prod': eqEd.ProdBigOperatorSymbol,
        'coProd': eqEd.CoProdBigOperatorSymbol,
        'bigVee': eqEd.BigVeeBigOperatorSymbol,
        'bigWedge': eqEd.BigWedgeBigOperatorSymbol
    }

    this.domObj = this.buildDomObj();
    this.childContainers = [];

    if (this.hasUpperLimit) {
        this.upperLimitContainer = new eqEd.BigOperatorUpperLimitContainer(this);
        this.domObj.append(this.upperLimitContainer.domObj);
        this.childContainers.push(this.upperLimitContainer);
    }
    if (this.hasLowerLimit) {
        this.lowerLimitContainer = new eqEd.BigOperatorLowerLimitContainer(this);
        this.domObj.append(this.lowerLimitContainer.domObj);
        this.childContainers.push(this.lowerLimitContainer)
    }
    
    this.symbol = new this.bigOperatorSymbolCtors[this.bigOperatorType](this);

    this.domObj.append(this.symbol.domObj);
    
    this.childNoncontainers = [this.symbol];

    this.padLeft = 0.05;
    this.padRight = 0.15;

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            var widthVal = 0;
            if (this.isInline) {
                var maxWidthList = [];
                if (this.hasUpperLimit) {
                    maxWidthList.push(this.upperLimitContainer.width);
                }
                if (this.hasLowerLimit) {
                    maxWidthList.push(this.lowerLimitContainer.width);
                }
                var limitWidth = (maxWidthList.length > 0) ? maxWidthList.max() : 0;
                widthVal = this.symbol.width + this.inlineLimitGap * fontHeight + limitWidth;
            } else {
                var maxWidthList = [];
                if (this.hasUpperLimit) {
                    maxWidthList.push(this.upperLimitContainer.width);
                }
                if (this.hasLowerLimit) {
                    maxWidthList.push(this.lowerLimitContainer.width);
                }
                maxWidthList.push(this.symbol.width);
                var maxWidth = maxWidthList.max();
                widthVal = maxWidth;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
            var topAlignVal = 0;
            if (this.isInline) {
                if (this.hasUpperLimit) {
                    if (this.upperLimitContainer.height > this.symbol.height * this.inlineUpperLimitOverlap) {
                        topAlignVal = 0.1 * this.symbol.height + this.upperLimitContainer.height;
                    } else {
                        topAlignVal = 0.5 * this.symbol.height;
                    }
                } else {
                    topAlignVal = 0.5 * this.symbol.height;
                }
            } else {
                if (this.hasUpperLimit) {
                    var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
                    topAlignVal = 0.5 * this.symbol.height + this.upperLimitContainer.height + this.upperLimitGap * fontHeight;
                } else {
                    topAlignVal = 0.5 * this.symbol.height;
                }
            }
            return topAlignVal;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
            var bottomAlignVal = 0;

            if (this.isInline) {
                if (this.hasLowerLimit) {
                    if (this.lowerLimitContainer.height > this.symbol.height * this.inlineLowerLimitOverlap) {
                        bottomAlignVal = 0.1 * this.symbol.height + this.lowerLimitContainer.height;
                    } else {
                        bottomAlignVal = 0.5 * this.symbol.height;
                    }
                } else {
                    bottomAlignVal = 0.5 * this.symbol.height;
                }
            } else {
                if (this.hasLowerLimit) {
                    var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
                    bottomAlignVal = 0.5 * this.symbol.height + this.lowerLimitContainer.height + this.lowerLimitGap * fontHeight;
                } else {
                    bottomAlignVal = 0.5 * this.symbol.height;
                }
            }
            return bottomAlignVal;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.BigOperatorWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.BigOperatorWrapper.prototype.constructor = eqEd.BigOperatorWrapper;
    eqEd.BigOperatorWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper bigOperatorWrapper"></div>')
    }
    eqEd.BigOperatorWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation, this.isInline, this.hasUpperLimit, this.hasLowerLimit, this.bigOperatorType);

        copy.childContainers = [];
        copy.domObj = copy.buildDomObj();

        if (copy.hasUpperLimit) {
            copy.upperLimitContainer = this.upperLimitContainer.clone();
            copy.upperLimitContainer.parent = copy;
            copy.domObj.append(copy.upperLimitContainer.domObj);
            copy.childContainers.push(copy.upperLimitContainer);
        }
        if (copy.hasLowerLimit) {
            copy.lowerLimitContainer = this.lowerLimitContainer.clone();
            copy.lowerLimitContainer.parent = copy;
            copy.domObj.append(copy.lowerLimitContainer.domObj);
            copy.childContainers.push(copy.lowerLimitContainer);
        }
        copy.symbol = new copy.bigOperatorSymbolCtors[copy.bigOperatorType](copy);

        copy.domObj.append(copy.symbol.domObj);

        copy.childNoncontainers = [copy.symbol];

        return copy;
    };
    eqEd.BigOperatorWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: this.bigOperatorType
        };
        if (!this.hasLowerLimit && !this.hasUpperLimit) {
            jsonObj.operands = null;
        } else if (this.hasLowerLimit && !this.hasUpperLimit) {
            jsonObj.operands = {
                lowerLimit: this.lowerLimitContainer.buildJsonObj()
            }
        } else if (!this.hasLowerLimit && this.hasUpperLimit) {
            jsonObj.operands = {
                upperLimit: this.upperLimitContainer.buildJsonObj()
            }
        } else {
            jsonObj.operands = {
                lowerLimit: this.lowerLimitContainer.buildJsonObj(),
                upperLimit: this.upperLimitContainer.buildJsonObj()
            }
        }
        return jsonObj;
    };
    eqEd.BigOperatorWrapper.constructFromJsonObj = function(jsonObj, equation) {
        var hasUpperLimit = (typeof jsonObj.operands.upperLimit !== "undefined");
        var hasLowerLimit = (typeof jsonObj.operands.lowerLimit !== "undefined");
        var bigOperatorWrapper = new eqEd.BigOperatorWrapper(equation, false, hasUpperLimit, hasLowerLimit, jsonObj.value);
        if (hasUpperLimit) {
            for (var i = 0; i < jsonObj.operands.upperLimit.length; i++) {
                var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.upperLimit[i].type);
                var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.upperLimit[i], equation);
                bigOperatorWrapper.upperLimitContainer.addWrappers([i, innerWrapper]);
            }
        }
        if (hasLowerLimit) {
            for (var i = 0; i < jsonObj.operands.lowerLimit.length; i++) {
                var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.lowerLimit[i].type);
                var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.lowerLimit[i], equation);
                bigOperatorWrapper.lowerLimitContainer.addWrappers([i, innerWrapper]);
            }
        }

        return bigOperatorWrapper;
    }
})();

/* End eq/js/equation-components/wrappers/bigOperatorWrapper.js*/

/* Begin eq/js/equation-components/containers/bigOperatorUpperLimitContainer.js*/

eqEd.BigOperatorUpperLimitContainer = function(parent) {
    eqEd.Container.call(this, parent);
    this.className = "eqEd.BigOperatorUpperLimitContainer";
    this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);
    
    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftVal = 0;
            if (this.parent.isInline) {
                leftVal = this.parent.symbol.width + this.parent.inlineLimitGap * fontHeight;
            } else {
                var maxWidthList = [];
                if (this.parent.hasUpperLimit) {
                    maxWidthList.push(this.parent.upperLimitContainer.width);
                }
                if (this.parent.hasLowerLimit) {
                    maxWidthList.push(this.parent.lowerLimitContainer.width);
                }
                maxWidthList.push(this.parent.symbol.width);
                var maxWidth = maxWidthList.max();
                leftVal = 0.5 * (maxWidth - this.width);
            }
            
            return leftVal;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var topVal = 0;
            if (this.parent.isInline) {
                var leftPartTopAlign = 0;
                if (this.height > this.parent.symbol.height * this.parent.inlineUpperLimitOverlap) {
                    leftPartTopAlign = (0.5 - this.parent.inlineLowerLimitOverlap) * this.parent.symbol.height + this.height;
                } else {
                    leftPartTopAlign = 0.5 * this.parent.symbol.height;
                }
                topVal = (this.parent.topAlign - this.parent.padTop * fontHeight) - leftPartTopAlign;
            } else {
                var leftPartTopAlign = this.height + 0.5 * this.parent.symbol.height + this.parent.upperLimitGap * fontHeight;
                topVal = (this.parent.topAlign - this.parent.padTop * fontHeight) - leftPartTopAlign;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var fontSizeVal = "";
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            if (actualParentContainer.fontSize === "fontSizeSmaller" || actualParentContainer.fontSize === "fontSizeSmallest") {
                fontSizeVal = "fontSizeSmallest";
            } else {
                fontSizeVal = "fontSizeSmaller";
            }
            return fontSizeVal;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.BigOperatorUpperLimitContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.BigOperatorUpperLimitContainer.prototype.constructor = eqEd.BigOperatorUpperLimitContainer;
    eqEd.BigOperatorUpperLimitContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer bigOperatorUpperLimitContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/bigOperatorUpperLimitContainer.js*/

/* Begin eq/js/equation-components/containers/bigOperatorLowerLimitContainer.js*/

eqEd.BigOperatorLowerLimitContainer = function(parent) {
    eqEd.Container.call(this, parent);
    this.className = "eqEd.BigOperatorLowerLimitContainer";
    this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);
    
    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftVal = 0;
            if (this.parent.isInline) {
                leftVal = this.parent.symbol.width + this.parent.inlineLimitGap * fontHeight;
            } else {
                var maxWidthList = [];
                if (this.parent.hasUpperLimit) {
                    maxWidthList.push(this.parent.upperLimitContainer.width);
                }
                if (this.parent.hasLowerLimit) {
                    maxWidthList.push(this.parent.lowerLimitContainer.width);
                }
                maxWidthList.push(this.parent.symbol.width);
                var maxWidth = maxWidthList.max();
                leftVal = 0.5 * (maxWidth - this.width);
            }
            return leftVal;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var topVal = 0;
            if (this.parent.isInline) {
                var additionalTopAlign = 0;
                if (this.height > this.parent.symbol.height * this.parent.inlineLowerLimitOverlap) {
                    additionalTopAlign = (0.5 - this.parent.inlineLowerLimitOverlap) * this.parent.symbol.height;
                } else {
                    additionalTopAlign = 0.5 * this.parent.symbol.height - this.height;
                }
                topVal = (this.parent.topAlign - this.parent.padTop * fontHeight) + additionalTopAlign;
            } else {
                topVal = (this.parent.topAlign - this.parent.padTop * fontHeight) + this.parent.symbol.height * 0.5 + this.parent.lowerLimitGap * fontHeight;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var fontSizeVal = "";
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            if (actualParentContainer.fontSize === "fontSizeSmaller" || actualParentContainer.fontSize === "fontSizeSmallest") {
                fontSizeVal = "fontSizeSmallest";
            } else {
                fontSizeVal = "fontSizeSmaller";
            }
            return fontSizeVal;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.BigOperatorLowerLimitContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.BigOperatorLowerLimitContainer.prototype.constructor = eqEd.BigOperatorLowerLimitContainer;
    eqEd.BigOperatorLowerLimitContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer bigOperatorLowerLimitContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/bigOperatorLowerLimitContainer.js*/

/* Begin eq/js/equation-components/containers/bigOperatorOperandContainer.js*/

eqEd.BigOperatorOperandContainer = function(parent) {
    eqEd.Container.call(this, parent);
    this.className = "eqEd.BigOperatorOperandContainer";
    this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);
    
    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftVal = 0;
            if (this.parent.isInline) {
                var maxWidthList = [];
                if (this.parent.hasUpperLimit) {
                    maxWidthList.push(this.parent.upperLimitContainer.width);
                }
                if (this.parent.hasLowerLimit) {
                    maxWidthList.push(this.parent.lowerLimitContainer.width);
                }
                var limitWidth = (maxWidthList.length > 0) ? maxWidthList.max() : 0;
                leftVal = this.parent.symbol.width + this.parent.inlineLimitGap * fontHeight + limitWidth + this.parent.inlineOperandGap * fontHeight;
            } else {
                var maxWidthList = [];
                if (this.parent.hasUpperLimit) {
                    maxWidthList.push(this.parent.upperLimitContainer.width);
                }
                if (this.parent.hasLowerLimit) {
                    maxWidthList.push(this.parent.lowerLimitContainer.width);
                }
                maxWidthList.push(this.parent.symbol.width);
                var maxWidth = maxWidthList.max();
                leftVal = maxWidth + this.parent.operandGap * fontHeight;
            }
            
            return leftVal;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var topVal = 0;
            if (this.wrappers.length > 0) {
                topVal = this.parent.topAlign - this.wrappers[this.maxTopAlignIndex].topAlign;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var fontSizeVal = "";
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            fontSizeVal = actualParentContainer.fontSize;
            return fontSizeVal;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.BigOperatorOperandContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.BigOperatorOperandContainer.prototype.constructor = eqEd.BigOperatorOperandContainer;
    eqEd.BigOperatorOperandContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer bigOperatorOperandContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/bigOperatorOperandContainer.js*/

/* Begin eq/js/equation-components/misc/bigOperatorSymbol.js*/

eqEd.BigOperatorSymbol = function(parent) {
    eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
    this.className = "eqEd.BigOperatorSymbol";

    this.domObj = this.buildDomObj();

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var leftVal = 0;
            if (this.parent.isInline) {
                leftVal = 0;
            } else {
                var maxWidthList = [];
                if (this.parent.hasUpperLimit) {
                    maxWidthList.push(this.parent.upperLimitContainer.width);
                }
                if (this.parent.hasLowerLimit) {
                    maxWidthList.push(this.parent.lowerLimitContainer.width);
                }
                maxWidthList.push(this.parent.symbol.width);
                var maxWidth = maxWidthList.max();
                leftVal = 0.5 * (maxWidth - this.width);
            }
            return leftVal;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var topVal = this.parent.topAlign - 0.5 * this.height;
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 1.5 * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.BigOperatorSymbol.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.BigOperatorSymbol.prototype.constructor = eqEd.BigOperatorSymbol;
})();

/* End eq/js/equation-components/misc/bigOperatorSymbol.js*/

/* Begin eq/js/equation-components/misc/sumBigOperatorSymbol.js*/

eqEd.SumBigOperatorSymbol = function(parent) {
    eqEd.BigOperatorSymbol.call(this, parent); // call super constructor.
    this.className = "eqEd.SumBigOperatorSymbol";

    this.domObj = this.buildDomObj();

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 0.94287111375 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.SumBigOperatorSymbol.prototype = Object.create(eqEd.BigOperatorSymbol.prototype);
    eqEd.SumBigOperatorSymbol.prototype.constructor = eqEd.SumBigOperatorSymbol;
    eqEd.SumBigOperatorSymbol.prototype.buildDomObj = function() {
        var htmlRep = '<div class="bigOperatorSymbol sumBigOperatorSymbol" style="width: 52.797009; height: 55.995998;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 52.797009 55.995998" preserveAspectRatio="none"><g transform="translate(-50.817524,-457.79474)"><g><path d="m 51.36436,513.79074 c -0.36456,-6e-5 -0.546839,-0.20968 -0.546836,-0.62886 -3e-6,-0.14588 0.05468,-0.27348 0.164051,-0.38279 L 71.351213,487.87071 50.981575,459.81803 c -0.10937,-0.10937 -0.164054,-0.20962 -0.164051,-0.30076 l 0,-1.23038 c -3e-6,-0.12759 0.05924,-0.24152 0.177722,-0.34177 0.118478,-0.10025 0.241516,-0.15038 0.369114,-0.15038 l 47.438016,0 4.812154,13.09672 -1.61316,0 c -0.92968,-2.55191 -2.337779,-4.57976 -4.224311,-6.08355 -1.886632,-1.5038 -4.042075,-2.59748 -6.466335,-3.28102 -2.424347,-0.68354 -4.784853,-1.10278 -7.081525,-1.25772 -2.296745,-0.15494 -5.094719,-0.23241 -8.393931,-0.2324 l -18.701789,0 18.483054,25.37318 c 0.10934,0.10934 0.164023,0.23694 0.164051,0.38279 -2.8e-5,0.14579 -0.05471,0.27339 -0.164051,0.38278 l -19.986853,24.41623 20.533689,0 c 3.244528,-6e-5 6.006047,-0.0775 8.284565,-0.23241 2.278443,-0.15499 4.620722,-0.56511 7.026841,-1.23038 2.406032,-0.66537 4.543247,-1.75904 6.411651,-3.28101 1.868304,-1.52208 3.239954,-3.55904 4.114944,-6.1109 l 1.61316,0 -4.812154,14.05369 z" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/sumBigOperatorSymbol.js*/

/* Begin eq/js/equation-components/misc/bigCapBigOperatorSymbol.js*/

eqEd.BigCapBigOperatorSymbol = function(parent) {
    eqEd.BigOperatorSymbol.call(this, parent); // call super constructor.
    this.className = "eqEd.BigCapBigOperatorSymbol";

    this.domObj = this.buildDomObj();

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 0.71482492204 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.BigCapBigOperatorSymbol.prototype = Object.create(eqEd.BigOperatorSymbol.prototype);
    eqEd.BigCapBigOperatorSymbol.prototype.constructor = eqEd.BigCapBigOperatorSymbol;
    eqEd.BigCapBigOperatorSymbol.prototype.buildDomObj = function() {
        var htmlRep = '<div class="bigOperatorSymbol bigCapBigOperatorSymbol" style="width: 200; height: 279.78879;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 200 279.78879" preserveAspectRatio="none"><g transform="translate(-259.34158,-210.57319)"><g><path d="m 352.34201,210.57944 c -11.10773,0.9043 -21.5904,3.34581 -31.44804,7.32454 -9.85778,3.9791 -19.04052,9.47042 -27.54827,16.47397 -8.79949,7.19138 -16.14903,16.00749 -22.04863,26.44835 -5.89965,10.44114 -9.7494,22.55704 -11.54927,36.34773 -0.0642,0.36428 -0.11359,2.61352 -0.14814,6.74773 -0.0346,4.1344 -0.0691,12.76842 -0.1037,25.90208 -0.0346,13.1338 -0.084,33.3819 -0.14814,60.74436 -0.004,32.99791 0.004,56.54642 0.025,70.64558 0.0208,14.09904 0.0792,21.1486 0.17499,21.14868 0.46246,2.3456 1.58739,4.25381 3.37479,5.72464 1.78737,1.47065 3.86224,2.22894 6.22461,2.27486 2.34983,-0.0501 4.39971,-0.80004 6.14962,-2.24986 1.74986,-1.45 2.89979,-3.29988 3.44978,-5.54965 0.0958,-0.0959 0.15413,-7.15379 0.17499,-21.17368 0.0208,-14.02 0.0291,-36.82689 0.025,-68.42072 -0.004,-29.84394 0.004,-52.30085 0.025,-67.3708 0.0208,-15.0698 0.0791,-23.07762 0.17499,-24.02349 1.26655,-15.12811 6.43289,-28.56893 15.49903,-40.32249 9.06604,-11.75328 21.03195,-20.44439 35.89776,-26.07337 4.61629,-1.70389 9.33265,-2.99548 14.14911,-3.87476 4.81627,-0.87894 9.63263,-1.32058 14.4491,-1.32491 22.78595,0.63346 41.45976,7.86633 56.0215,21.69864 14.56141,13.8326 22.68589,30.46488 24.37348,49.89689 0.0956,0.94587 0.15396,8.95369 0.17499,24.02349 0.0206,15.06996 0.029,37.52687 0.025,67.3708 -0.004,31.59383 0.004,54.40072 0.025,68.42072 0.0206,14.01988 0.079,21.07777 0.17499,21.17368 0.54977,2.24978 1.6997,4.09966 3.44978,5.54965 1.7497,1.44982 3.79957,2.19977 6.14962,2.24986 2.36215,-0.0459 4.43702,-0.8042 6.22461,-2.27486 1.78718,-1.47083 2.91211,-3.37904 3.37479,-5.72464 0.0956,-8e-5 0.15395,-7.04963 0.17499,-21.14868 0.0206,-14.09916 0.0289,-37.64767 0.025,-70.64558 -0.045,-24.62447 -0.0804,-43.4587 -0.10625,-56.50273 -0.0263,-13.0439 -0.0742,-22.24123 -0.14374,-27.59202 -0.07,-5.35061 -0.1929,-8.7983 -0.36872,-10.3431 -0.17624,-1.5446 -0.43664,-3.12991 -0.7812,-4.75596 -1.96676,-11.9158 -6.13316,-23.08176 -12.49922,-33.49791 -6.36646,-10.41586 -14.53261,-19.38196 -24.49847,-26.89831 -14.83256,-11.23245 -31.96481,-17.96536 -51.39679,-20.19874 -1.30837,-0.0956 -3.19159,-0.15397 -5.64965,-0.17499 -2.45829,-0.0207 -4.84147,-0.029 -7.14955,-0.025 z" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/bigCapBigOperatorSymbol.js*/

/* Begin eq/js/equation-components/misc/bigCupBigOperatorSymbol.js*/

eqEd.BigCupBigOperatorSymbol = function(parent) {
    eqEd.BigOperatorSymbol.call(this, parent); // call super constructor.
    this.className = "eqEd.BigCupBigOperatorSymbol";

    this.domObj = this.buildDomObj();

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 0.71476115809 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.BigCupBigOperatorSymbol.prototype = Object.create(eqEd.BigOperatorSymbol.prototype);
    eqEd.BigCupBigOperatorSymbol.prototype.constructor = eqEd.BigCupBigOperatorSymbol;
    eqEd.BigCupBigOperatorSymbol.prototype.buildDomObj = function() {
        var htmlRep = '<div class="bigOperatorSymbol bigCupBigOperatorSymbol" style="width: 200; height: 279.81375;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 200 279.81375" preserveAspectRatio="none"><g transform="translate(-382.19872,-150.51443)"><g><path d="m 382.40496,158.31394 c -0.0958,0.096 -0.15417,7.2039 -0.17499,21.32367 -0.0208,14.1201 -0.0292,37.67694 -0.025,70.67059 0.0448,24.62457 0.0802,43.45879 0.10624,56.50272 0.026,13.04399 0.0739,22.24133 0.14375,27.59202 0.0698,5.35071 0.19268,8.79841 0.36872,10.34311 0.17602,1.54469 0.43642,3.13001 0.7812,4.75595 1.4249,8.84526 4.12473,17.35306 8.09949,25.52341 3.97473,8.17028 9.07441,15.72813 15.29905,22.67358 7.8078,8.46607 16.89056,15.4323 27.2483,20.89869 10.35761,5.46624 21.34025,9.03268 32.94794,10.69934 2.91222,0.47904 5.33707,0.77069 7.27455,0.87494 1.93727,0.10407 4.51211,0.14573 7.72451,0.12499 9.07015,-0.0126 17.17797,-0.88753 24.32348,-2.62484 7.14525,-1.73748 14.10314,-4.26231 20.8737,-7.57452 9.72422,-4.82477 18.32368,-10.87439 25.79839,-18.14886 7.47435,-7.27461 13.67395,-15.62408 18.59884,-25.04844 2.5913,-5.05805 4.70783,-10.29106 6.3496,-15.69902 1.64136,-5.40801 2.85795,-11.24097 3.64977,-17.49891 0.064,-0.36417 0.11336,-2.61341 0.14814,-6.74773 0.0344,-4.1343 0.0689,-12.76832 0.10369,-25.90208 0.0344,-13.13371 0.0837,-33.3818 0.14815,-60.74435 0.004,-32.99365 -0.004,-56.55049 -0.025,-70.67059 -0.021,-14.11978 -0.0794,-21.22766 -0.17498,-21.32367 -0.46268,-2.24967 -1.58761,-4.09955 -3.37479,-5.54965 -1.78759,-1.44972 -3.86246,-2.19967 -6.22461,-2.24986 -2.35006,0.0502 -4.39993,0.80014 -6.14962,2.24986 -1.75008,1.4501 -2.90001,3.29998 -3.44978,5.54965 -0.096,0.096 -0.15435,7.1539 -0.17499,21.17368 -0.021,14.02011 -0.0294,36.82699 -0.025,68.42073 0.004,29.84403 -0.004,52.30094 -0.025,67.37079 -0.021,15.0699 -0.0794,23.07773 -0.17499,24.0235 -1.27094,15.1282 -6.42895,28.56901 -15.47403,40.32248 -9.04544,11.75338 -20.95302,20.4445 -35.72277,26.07337 -4.61235,1.70399 -9.33705,2.99557 -14.17411,3.87475 -4.83732,0.87905 -9.71201,1.32069 -14.62409,1.32493 -22.78616,-0.63337 -41.45998,-7.86624 -56.0215,-21.69865 -14.56163,-13.83251 -22.68611,-30.46478 -24.37348,-49.89688 -0.0959,-0.94578 -0.15418,-8.95361 -0.17499,-24.02351 -0.0209,-15.06984 -0.0292,-37.52675 -0.025,-67.37078 0.004,-31.59374 -0.004,-54.40063 -0.025,-68.42073 -0.0209,-14.01978 -0.0792,-21.07767 -0.17498,-21.17368 -0.55833,-2.33717 -1.74159,-4.21205 -3.54978,-5.62465 -1.80825,-1.41222 -3.89145,-2.13717 -6.24961,-2.17486 -2.54152,0.0752 -4.65806,0.87514 -6.3496,2.39985 -1.69158,1.52509 -2.70818,3.32498 -3.04981,5.39966 z" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/bigCupBigOperatorSymbol.js*/

/* Begin eq/js/equation-components/misc/bigSqCapBigOperatorSymbol.js*/

eqEd.BigSqCapBigOperatorSymbol = function(parent) {
    eqEd.BigOperatorSymbol.call(this, parent); // call super constructor.
    this.className = "eqEd.BigSqCapBigOperatorSymbol";

    this.domObj = this.buildDomObj();

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 0.71426980882 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.BigSqCapBigOperatorSymbol.prototype = Object.create(eqEd.BigOperatorSymbol.prototype);
    eqEd.BigSqCapBigOperatorSymbol.prototype.constructor = eqEd.BigSqCapBigOperatorSymbol;
    eqEd.BigSqCapBigOperatorSymbol.prototype.buildDomObj = function() {
        var htmlRep = '<div class="bigOperatorSymbol bigSqCapBigOperatorSymbol" style="width: 39.999485; height: 56.000526;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 39.999485 56.000526" preserveAspectRatio="none"><g transform="translate(-336.48568,-397.21981)"><g transform="scale(1,-1)"><path d="m 336.52568,-451.66036 c -5e-4,0.12053 -0.004,1.44841 -0.0104,3.98366 -0.006,2.5353 -0.0129,5.79501 -0.0193,9.77913 -0.006,3.98416 -0.01,8.20978 -0.0104,12.67687 4.9e-4,4.46711 0.004,8.69273 0.0104,12.67688 0.006,3.98414 0.0128,7.24384 0.0193,9.77913 0.006,2.53526 0.01,3.86314 0.0104,3.98365 0.20666,0.72664 0.71332,1.2333 1.51998,1.51998 l 18.31976,0.04 c 6.62322,8.2e-4 11.36649,-8.5e-4 14.22982,-0.005 2.86326,-0.004 4.30657,-0.0159 4.32994,-0.035 0.36829,-0.0858 0.69162,-0.26418 0.96999,-0.53499 0.27829,-0.27085 0.46162,-0.59918 0.54999,-0.98499 4.5e-4,-0.12051 0.004,-1.44839 0.0104,-3.98365 0.006,-2.53528 0.0128,-5.79499 0.0193,-9.77913 0.006,-3.98415 0.01,-8.20977 0.0104,-12.67688 -5.3e-4,-4.46709 -0.004,-8.69271 -0.0104,-12.67687 -0.006,-3.98412 -0.0129,-7.24383 -0.0193,-9.77913 -0.006,-2.53524 -0.01,-3.86313 -0.0104,-3.98366 -0.11004,-0.44995 -0.34004,-0.81995 -0.68999,-1.10998 -0.35004,-0.28996 -0.76003,-0.43996 -1.22998,-0.45 -0.47004,0.01 -0.88003,0.16004 -1.22999,0.45 -0.35003,0.29003 -0.58003,0.66003 -0.68999,1.10998 -0.0192,-0.008 -0.0309,1.96835 -0.035,5.92993 -0.004,3.96164 -0.006,10.43822 -0.005,19.42975 l 0,25.15967 -32.15958,0 0,-25.15967 c 8.2e-4,-8.99153 -8.4e-4,-15.46811 -0.005,-19.42975 -0.004,-3.96158 -0.0158,-5.93822 -0.035,-5.92993 -0.11167,-0.46745 -0.34834,-0.84245 -0.70999,-1.12498 -0.36167,-0.28246 -0.77833,-0.42746 -1.24999,-0.435 -0.50333,0.015 -0.91666,0.17504 -1.23998,0.48 -0.32333,0.30503 -0.53666,0.66503 -0.63999,1.07998 z" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/bigSqCapBigOperatorSymbol.js*/

/* Begin eq/js/equation-components/misc/bigSqCupBigOperatorSymbol.js*/

eqEd.BigSqCupBigOperatorSymbol = function(parent) {
    eqEd.BigOperatorSymbol.call(this, parent); // call super constructor.
    this.className = "eqEd.BigSqCupBigOperatorSymbol";

    this.domObj = this.buildDomObj();

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 0.71426980882 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.BigSqCupBigOperatorSymbol.prototype = Object.create(eqEd.BigOperatorSymbol.prototype);
    eqEd.BigSqCupBigOperatorSymbol.prototype.constructor = eqEd.BigSqCupBigOperatorSymbol;
    eqEd.BigSqCupBigOperatorSymbol.prototype.buildDomObj = function() {
        var htmlRep = '<div class="bigOperatorSymbol bigSqCupBigOperatorSymbol" style="width: 39.999485; height: 56.000526;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 39.999485 56.000526" preserveAspectRatio="none"><g transform="translate(-285.05712,-397.21982)"><g><path d="m 285.09712,398.7798 c -5e-4,0.12053 -0.004,1.44841 -0.0104,3.98365 -0.006,2.53531 -0.0128,5.79501 -0.0193,9.77913 -0.006,3.98417 -0.01,8.20979 -0.0104,12.67688 4.9e-4,4.46711 0.004,8.69273 0.0104,12.67687 0.006,3.98414 0.0128,7.24385 0.0193,9.77914 0.006,2.53526 0.01,3.86314 0.0104,3.98365 0.20666,0.72664 0.71332,1.2333 1.51998,1.51998 l 18.31976,0.04 c 6.62322,8.1e-4 11.36649,-8.5e-4 14.22982,-0.005 2.86326,-0.004 4.30657,-0.0159 4.32994,-0.035 0.36829,-0.0858 0.69162,-0.26418 0.96999,-0.53499 0.27829,-0.27085 0.46162,-0.59918 0.54999,-0.98499 4.6e-4,-0.12051 0.004,-1.44839 0.0104,-3.98365 0.006,-2.53529 0.0128,-5.795 0.0193,-9.77914 0.006,-3.98414 0.01,-8.20976 0.0104,-12.67687 -5.3e-4,-4.46709 -0.004,-8.69271 -0.0104,-12.67688 -0.006,-3.98412 -0.0129,-7.24382 -0.0193,-9.77913 -0.006,-2.53524 -0.01,-3.86312 -0.0104,-3.98365 -0.11004,-0.44996 -0.34003,-0.81995 -0.68999,-1.10999 -0.35003,-0.28996 -0.76003,-0.43995 -1.22998,-0.44999 -0.47004,0.01 -0.88003,0.16004 -1.22999,0.44999 -0.35003,0.29004 -0.58003,0.66003 -0.68999,1.10999 -0.0192,-0.008 -0.0309,1.96834 -0.035,5.92992 -0.004,3.96165 -0.006,10.43822 -0.005,19.42975 l 0,25.15968 -32.15958,0 0,-25.15968 c 8.3e-4,-8.99153 -8.4e-4,-15.46811 -0.005,-19.42975 -0.004,-3.96158 -0.0158,-5.93822 -0.035,-5.92992 -0.11167,-0.46746 -0.34834,-0.84245 -0.70999,-1.12499 -0.36167,-0.28246 -0.77833,-0.42745 -1.24999,-0.43499 -0.50333,0.015 -0.91665,0.17503 -1.23998,0.47999 -0.32333,0.30504 -0.53666,0.66503 -0.63999,1.07999 z" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/bigSqCupBigOperatorSymbol.js*/

/* Begin eq/js/equation-components/misc/bigSqCupBigOperatorSymbol.js*/

eqEd.BigSqCupBigOperatorSymbol = function(parent) {
    eqEd.BigOperatorSymbol.call(this, parent); // call super constructor.
    this.className = "eqEd.BigSqCupBigOperatorSymbol";

    this.domObj = this.buildDomObj();

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 0.71426980882 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.BigSqCupBigOperatorSymbol.prototype = Object.create(eqEd.BigOperatorSymbol.prototype);
    eqEd.BigSqCupBigOperatorSymbol.prototype.constructor = eqEd.BigSqCupBigOperatorSymbol;
    eqEd.BigSqCupBigOperatorSymbol.prototype.buildDomObj = function() {
        var htmlRep = '<div class="bigOperatorSymbol bigSqCupBigOperatorSymbol" style="width: 39.999485; height: 56.000526;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 39.999485 56.000526" preserveAspectRatio="none"><g transform="translate(-285.05712,-397.21982)"><g><path d="m 285.09712,398.7798 c -5e-4,0.12053 -0.004,1.44841 -0.0104,3.98365 -0.006,2.53531 -0.0128,5.79501 -0.0193,9.77913 -0.006,3.98417 -0.01,8.20979 -0.0104,12.67688 4.9e-4,4.46711 0.004,8.69273 0.0104,12.67687 0.006,3.98414 0.0128,7.24385 0.0193,9.77914 0.006,2.53526 0.01,3.86314 0.0104,3.98365 0.20666,0.72664 0.71332,1.2333 1.51998,1.51998 l 18.31976,0.04 c 6.62322,8.1e-4 11.36649,-8.5e-4 14.22982,-0.005 2.86326,-0.004 4.30657,-0.0159 4.32994,-0.035 0.36829,-0.0858 0.69162,-0.26418 0.96999,-0.53499 0.27829,-0.27085 0.46162,-0.59918 0.54999,-0.98499 4.6e-4,-0.12051 0.004,-1.44839 0.0104,-3.98365 0.006,-2.53529 0.0128,-5.795 0.0193,-9.77914 0.006,-3.98414 0.01,-8.20976 0.0104,-12.67687 -5.3e-4,-4.46709 -0.004,-8.69271 -0.0104,-12.67688 -0.006,-3.98412 -0.0129,-7.24382 -0.0193,-9.77913 -0.006,-2.53524 -0.01,-3.86312 -0.0104,-3.98365 -0.11004,-0.44996 -0.34003,-0.81995 -0.68999,-1.10999 -0.35003,-0.28996 -0.76003,-0.43995 -1.22998,-0.44999 -0.47004,0.01 -0.88003,0.16004 -1.22999,0.44999 -0.35003,0.29004 -0.58003,0.66003 -0.68999,1.10999 -0.0192,-0.008 -0.0309,1.96834 -0.035,5.92992 -0.004,3.96165 -0.006,10.43822 -0.005,19.42975 l 0,25.15968 -32.15958,0 0,-25.15968 c 8.3e-4,-8.99153 -8.4e-4,-15.46811 -0.005,-19.42975 -0.004,-3.96158 -0.0158,-5.93822 -0.035,-5.92992 -0.11167,-0.46746 -0.34834,-0.84245 -0.70999,-1.12499 -0.36167,-0.28246 -0.77833,-0.42745 -1.24999,-0.43499 -0.50333,0.015 -0.91665,0.17503 -1.23998,0.47999 -0.32333,0.30504 -0.53666,0.66503 -0.63999,1.07999 z" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/bigSqCupBigOperatorSymbol.js*/

/* Begin eq/js/equation-components/misc/prodBigOperatorSymbol.js*/

eqEd.ProdBigOperatorSymbol = function(parent) {
    eqEd.BigOperatorSymbol.call(this, parent); // call super constructor.
    this.className = "eqEd.ProdBigOperatorSymbol";

    this.domObj = this.buildDomObj();

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            return 0.83214285669 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.ProdBigOperatorSymbol.prototype = Object.create(eqEd.BigOperatorSymbol.prototype);
    eqEd.ProdBigOperatorSymbol.prototype.constructor = eqEd.ProdBigOperatorSymbol;
    eqEd.ProdBigOperatorSymbol.prototype.buildDomObj = function() {
        var htmlRep = '<div class="bigOperatorSymbol prodBigOperatorSymbol" style="width: 46.5994; height: 55.999279;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 46.5994 55.999279" preserveAspectRatio="none"><g transform="translate(-256.52568,-351.50552)"><g><path d="m 296.60516,401.94488 0,-44.91943 c 0.33245,-1.22495 1.02744,-2.09494 2.08497,-2.60996 1.05744,-0.51496 2.40243,-0.76496 4.03495,-0.74999 l 0.4,0 0,-2.15998 -46.5994,0 0,2.15998 0.39999,0 c 0.72749,0.003 1.41248,0.0575 2.05497,0.16499 0.64249,0.10754 1.19748,0.25254 1.66498,0.435 0.69915,0.29253 1.22081,0.65752 1.56498,1.09498 0.34416,0.43753 0.63582,0.99253 0.87499,1.66498 l 0,44.91943 -0.12,0.35999 c -0.38917,1.10664 -1.10082,1.89329 -2.13497,2.35997 -1.03416,0.46665 -2.33581,0.69331 -3.90495,0.67999 l -0.39999,0 0,2.15997 19.83974,0 0,-2.15997 -0.39999,0 c -1.6375,0.0158 -2.99248,-0.23584 -4.06495,-0.75499 -1.0725,-0.51917 -1.75749,-1.40083 -2.05497,-2.64496 l -0.04,-24.11969 0,-24.15969 20.03974,0 0,48.27938 -0.12,0.35999 c -0.3892,1.10664 -1.10086,1.89329 -2.13497,2.35997 -1.03419,0.46665 -2.33584,0.69331 -3.90495,0.67999 l -0.4,0 0,2.15997 19.83975,0 0,-2.15997 -0.4,0 c -1.63752,0.0158 -2.99251,-0.23584 -4.06495,-0.75499 -1.07253,-0.51917 -1.75752,-1.40083 -2.05497,-2.64496 z" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/prodBigOperatorSymbol.js*/

/* Begin eq/js/equation-components/misc/coProdBigOperatorSymbol.js*/

eqEd.CoProdBigOperatorSymbol = function(parent) {
    eqEd.BigOperatorSymbol.call(this, parent); // call super constructor.
    this.className = "eqEd.CoProdBigOperatorSymbol";

    this.domObj = this.buildDomObj();

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            return 0.83214285669 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.CoProdBigOperatorSymbol.prototype = Object.create(eqEd.BigOperatorSymbol.prototype);
    eqEd.CoProdBigOperatorSymbol.prototype.constructor = eqEd.CoProdBigOperatorSymbol;
    eqEd.CoProdBigOperatorSymbol.prototype.buildDomObj = function() {
        var htmlRep = '<div class="bigOperatorSymbol coProdBigOperatorSymbol" style="width: 46.5994; height: 55.999279;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 46.5994 55.999279" preserveAspectRatio="none"><g transform="translate(-110.8114,-368.64838)"><g><path d="m 150.89088,419.08773 0,-44.91942 c 0.33246,-1.22496 1.02745,-2.09494 2.08497,-2.60997 1.05745,-0.51496 2.40243,-0.76496 4.03495,-0.74999 l 0.4,0 0,-2.15997 -19.83975,0 0,2.15997 0.4,0 c 0.72746,0.003 1.41245,0.0575 2.05497,0.165 0.64246,0.10753 1.19745,0.25253 1.66498,0.43499 0.69912,0.29253 1.22078,0.65753 1.56498,1.09499 0.34413,0.43753 0.63579,0.99252 0.87499,1.66498 l 0,48.31937 -20.03974,0 0,-24.15968 0.04,-24.15969 c 0.33248,-1.22496 1.02747,-2.09494 2.08497,-2.60997 1.05747,-0.51496 2.40245,-0.76496 4.03495,-0.74999 l 0.39999,0 0,-2.15997 -19.83974,0 0,2.15997 0.39999,0 c 0.72749,0.003 1.41248,0.0575 2.05498,0.165 0.64248,0.10753 1.19747,0.25253 1.66497,0.43499 0.69916,0.29253 1.22081,0.65753 1.56498,1.09499 0.34416,0.43753 0.63582,0.99252 0.87499,1.66498 l 0,44.91942 -0.12,0.35999 c -0.38917,1.10664 -1.10082,1.8933 -2.13497,2.35997 -1.03416,0.46665 -2.33581,0.69331 -3.90495,0.67999 l -0.39999,0 0,2.15998 46.5994,0 0,-2.15998 -0.4,0 c -1.63752,0.0158 -2.9925,-0.23584 -4.06495,-0.75499 -1.07252,-0.51917 -1.75751,-1.40082 -2.05497,-2.64496 z" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/coProdBigOperatorSymbol.js*/

/* Begin eq/js/equation-components/misc/bigVeeBigOperatorSymbol.js*/

eqEd.BigVeeBigOperatorSymbol = function(parent) {
    eqEd.BigOperatorSymbol.call(this, parent); // call super constructor.
    this.className = "eqEd.BigVeeBigOperatorSymbol";

    this.domObj = this.buildDomObj();

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 0.71433033986 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.BigVeeBigOperatorSymbol.prototype = Object.create(eqEd.BigOperatorSymbol.prototype);
    eqEd.BigVeeBigOperatorSymbol.prototype.constructor = eqEd.BigVeeBigOperatorSymbol;
    eqEd.BigVeeBigOperatorSymbol.prototype.buildDomObj = function() {
        var htmlRep = '<div class="bigOperatorSymbol bigVeeBigOperatorSymbol" style="width: 52.797009; height: 55.995998;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 40.001984 55.999279" preserveAspectRatio="none"><g transform="translate(-222.19872,-417.21982)"><g><path d="m 222.19997,419.21979 c -8.3e-4,0.0617 8.3e-4,0.12837 0.005,0.2 0.004,0.0717 0.0158,0.13837 0.035,0.2 0.0476,0.14642 0.50907,1.48585 1.38443,4.01828 0.87535,2.5325 1.99904,5.77967 3.37107,9.74154 1.37201,3.96191 2.8268,8.16019 4.36438,12.59484 1.53756,4.43467 2.99236,8.62739 4.36439,12.57817 1.372,3.95078 2.49569,7.18129 3.37107,9.69154 0.87534,2.51023 1.33681,3.82188 1.38443,3.93495 0.18581,0.32331 0.43414,0.57664 0.74499,0.75999 0.31081,0.18331 0.64913,0.27665 1.01498,0.28 0.36581,-0.008 0.69414,-0.10252 0.98499,-0.285 0.29081,-0.18251 0.50914,-0.40751 0.65499,-0.67499 0.0477,-0.10677 0.51111,-1.41768 1.39017,-3.93273 0.879,-2.51507 2.0072,-5.75373 3.38458,-9.71598 1.37733,-3.96227 2.83738,-8.16758 4.38013,-12.61595 1.5427,-4.44836 3.00163,-8.65923 4.3768,-12.63262 1.3751,-3.97335 2.49996,-7.22867 3.37459,-9.76598 0.87454,-2.53725 1.33238,-3.87594 1.3735,-4.01606 0.0191,-0.0616 0.0308,-0.1283 0.035,-0.2 0.004,-0.0716 0.006,-0.13829 0.005,-0.2 -0.01,-0.50162 -0.19004,-0.94828 -0.54,-1.33998 -0.35003,-0.39162 -0.81003,-0.59829 -1.37998,-0.61999 -0.36587,0.003 -0.7042,0.0967 -1.01499,0.28 -0.31086,0.18336 -0.55919,0.43669 -0.74499,0.75999 -0.0447,0.10448 -0.43978,1.22002 -1.18517,3.34662 -0.74546,2.12667 -1.7331,4.95774 -2.96292,8.49322 -1.22989,3.53553 -2.59383,7.46881 -4.0918,11.79985 l -8.0799,23.3197 -8.07989,-23.3197 c -1.49802,-4.33104 -2.86195,-8.26432 -4.0918,-11.79985 -1.22987,-3.53548 -2.21751,-6.36655 -2.96293,-8.49322 -0.74543,-2.12661 -1.14048,-3.24215 -1.18517,-3.34662 -0.16833,-0.3233 -0.41166,-0.57663 -0.72999,-0.75999 -0.31833,-0.1833 -0.66166,-0.27663 -1.02998,-0.28 -0.5875,0.0234 -1.05249,0.2367 -1.39499,0.63999 -0.34249,0.40337 -0.51749,0.85669 -0.52499,1.35998 z" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/bigVeeBigOperatorSymbol.js*/

/* Begin eq/js/equation-components/misc/bigWedgeBigOperatorSymbol.js*/

eqEd.BigWedgeBigOperatorSymbol = function(parent) {
    eqEd.BigOperatorSymbol.call(this, parent); // call super constructor.
    this.className = "eqEd.BigWedgeBigOperatorSymbol";

    this.domObj = this.buildDomObj();

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 0.71433033986 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.BigWedgeBigOperatorSymbol.prototype = Object.create(eqEd.BigOperatorSymbol.prototype);
    eqEd.BigWedgeBigOperatorSymbol.prototype.constructor = eqEd.BigWedgeBigOperatorSymbol;
    eqEd.BigWedgeBigOperatorSymbol.prototype.buildDomObj = function() {
        var htmlRep = '<div class="bigOperatorSymbol bigWedgeBigOperatorSymbol" style="width: 52.797009; height: 55.995998;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 40.001984 55.999279" preserveAspectRatio="none"><g transform="translate(-185.05587,-445.79123)"><g><path d="m 225.0566,499.83053 c 7.9e-4,-0.0617 -8.7e-4,-0.12834 -0.005,-0.19999 -0.004,-0.0717 -0.0159,-0.13835 -0.035,-0.2 -0.0411,-0.14011 -0.49895,-1.47879 -1.3735,-4.01606 -0.87462,-2.53729 -1.99949,-5.79262 -3.37458,-9.76599 -1.37518,-3.97337 -2.83411,-8.18424 -4.3768,-12.63261 -1.54276,-4.44835 -3.0028,-8.65367 -4.38013,-12.61595 -1.37738,-3.96224 -2.50558,-7.2009 -3.38458,-9.71599 -0.87906,-2.51502 -1.34245,-3.82593 -1.39017,-3.93272 -0.16752,-0.28496 -0.40252,-0.51496 -0.70499,-0.68999 -0.30252,-0.17496 -0.62752,-0.26496 -0.97499,-0.27 -0.40418,0.007 -0.75584,0.10337 -1.05499,0.29 -0.29918,0.1867 -0.52084,0.42336 -0.66499,0.70999 -0.0476,0.11972 -0.50908,1.43699 -1.38442,3.9518 -0.87538,2.51487 -1.99907,5.74915 -3.37107,9.70284 -1.37203,3.95373 -2.82683,8.14873 -4.36439,12.58502 -1.53758,4.43631 -2.99238,8.63575 -4.36439,12.59835 -1.37202,3.9626 -2.49571,7.21021 -3.37107,9.74284 -0.87536,2.53261 -1.33683,3.8721 -1.38442,4.01847 -0.0192,0.0783 -0.0308,0.15165 -0.035,0.22 -0.004,0.0683 -0.006,0.14165 -0.005,0.21999 0.0142,0.53665 0.20583,0.99331 0.57499,1.36999 0.36916,0.37664 0.83082,0.5733 1.38498,0.58999 0.36666,-0.003 0.70332,-0.0975 1.00999,-0.285 0.30666,-0.18751 0.54332,-0.45251 0.70999,-0.79499 0.0447,-0.0914 0.43974,-1.19753 1.18517,-3.31847 0.74542,-2.12098 1.73306,-4.94859 2.96293,-8.48286 1.22985,-3.53427 2.59378,-7.46706 4.0918,-11.79836 l 8.07989,-23.3197 8.0799,23.3197 c 1.49797,4.3313 2.8619,8.26409 4.0918,11.79836 1.22982,3.53427 2.21746,6.36188 2.96292,8.48286 0.74538,2.12094 1.14044,3.2271 1.18517,3.31847 0.18579,0.34248 0.43412,0.60748 0.74499,0.79499 0.31079,0.18748 0.64912,0.28248 1.01499,0.285 0.56995,-0.0217 1.02994,-0.22835 1.37998,-0.61999 0.34995,-0.39168 0.52995,-0.83834 0.53999,-1.33999 z" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/bigWedgeBigOperatorSymbol.js*/

/* Begin eq/js/equation-components/wrappers/integralWrapper.js*/

eqEd.IntegralWrapper = function(equation, isInline, hasUpperLimit, hasLowerLimit, integralType) {
    eqEd.Wrapper.call(this, equation); // call super constructor.
    this.className = "eqEd.IntegralWrapper";

    this.isInline = isInline;
    this.hasUpperLimit = hasUpperLimit;
    this.hasLowerLimit = hasLowerLimit;
    this.integralType = integralType;

    this.upperLimitGap = 0.1;
    this.lowerLimitGap = 0.2;

    this.inlineUpperLimitOverlap = 0.25;
    this.inlineLowerLimitOverlap = 0.25;
    this.inlineLimitGap = 0.15;

    this.numIntegrals = 0;
    if (this.integralType === "single"
        || this.integralType === "singleContour") {
        this.numIntegrals = 1;
    } else if (this.integralType === "double"
        || this.integralType === "doubleContour") {
        this.numIntegrals = 2;
    } else if (this.integralType === "triple"
        || this.integralType === "tripleContour") {
        this.numIntegrals = 3;
    }

    this.integralSymbolCtors = {
        'single': eqEd.IntegralSymbol,
        'double': eqEd.DoubleIntegralSymbol,
        'triple': eqEd.TripleIntegralSymbol,
        'singleContour': eqEd.ContourIntegralSymbol,
        'doubleContour': eqEd.ContourDoubleIntegralSymbol,
        'tripleContour': eqEd.ContourTripleIntegralSymbol
    }

    this.domObj = this.buildDomObj();
    this.childContainers = [];

    if (this.hasUpperLimit) {
        this.upperLimitContainer = new eqEd.IntegralUpperLimitContainer(this);
        this.domObj.append(this.upperLimitContainer.domObj);
        this.childContainers.push(this.upperLimitContainer);
    }
    if (this.hasLowerLimit) {
        this.lowerLimitContainer = new eqEd.IntegralLowerLimitContainer(this);
        this.domObj.append(this.lowerLimitContainer.domObj);
        this.childContainers.push(this.lowerLimitContainer)
    }
    
    this.symbol = new this.integralSymbolCtors[this.integralType](this);
    this.domObj.append(this.symbol.domObj);
    
    this.childNoncontainers = [this.symbol];

    this.padLeft = 0.15;
    this.padRight = 0.15;


    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            var widthVal = 0;
            if (this.isInline) {
                var maxWidthList = [];
                if (this.hasUpperLimit) {
                    maxWidthList.push(this.upperLimitContainer.width);
                }
                if (this.hasLowerLimit) {
                    maxWidthList.push(this.lowerLimitContainer.width - this.lowerLimitContainer.inlineLeftOverlap * fontHeight);
                }
                var limitWidth = (maxWidthList.length > 0) ? maxWidthList.max() : 0;
                widthVal = this.symbol.width + this.inlineLimitGap * fontHeight + limitWidth;
            } else {
                var maxWidthList = [];
                if (this.hasUpperLimit) {
                    maxWidthList.push(this.upperLimitContainer.width);
                }
                if (this.hasLowerLimit) {
                    maxWidthList.push(this.lowerLimitContainer.width);
                }
                maxWidthList.push(this.symbol.width);
                var maxWidth = maxWidthList.max();
                widthVal = maxWidth;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
            var topAlignVal = 0;
            if (this.isInline) {
                if (this.hasUpperLimit) {
                    if (this.upperLimitContainer.height > this.symbol.height * this.inlineUpperLimitOverlap) {
                        topAlignVal = (0.5 - this.inlineUpperLimitOverlap) * this.symbol.height + this.upperLimitContainer.height;
                    } else {
                        topAlignVal = 0.5 * this.symbol.height;
                    }
                } else {
                    topAlignVal = 0.5 * this.symbol.height;
                }
            } else {
                if (this.hasUpperLimit) {
                    var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
                    topAlignVal = 0.5 * this.symbol.height + this.upperLimitContainer.height + this.upperLimitGap * fontHeight;
                } else {
                    topAlignVal = 0.5 * this.symbol.height;
                }
            }
            return topAlignVal;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
            var bottomAlignVal = 0;
            if (this.isInline) {
                if (this.hasLowerLimit) {
                    if (this.lowerLimitContainer.height > this.symbol.height * this.inlineLowerLimitOverlap) {
                        bottomAlignVal = (0.5 - this.inlineLowerLimitOverlap) * this.symbol.height + this.lowerLimitContainer.height;
                    } else {
                        bottomAlignVal = 0.5 * this.symbol.height;
                    }
                } else {
                    bottomAlignVal = 0.5 * this.symbol.height;
                }
            } else {
                if (this.hasLowerLimit) {
                    var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
                    bottomAlignVal = 0.5 * this.symbol.height + this.lowerLimitContainer.height + this.lowerLimitGap * fontHeight;
                } else {
                    bottomAlignVal = 0.5 * this.symbol.height;
                }
            }
            return bottomAlignVal;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.IntegralWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.IntegralWrapper.prototype.constructor = eqEd.IntegralWrapper;
    eqEd.IntegralWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper integralWrapper"></div>')
    };
    eqEd.IntegralWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation, this.isInline, this.hasUpperLimit, this.hasLowerLimit, this.integralType);

        copy.childContainers = [];
        copy.domObj = copy.buildDomObj();

        if (copy.hasUpperLimit) {
            copy.upperLimitContainer = this.upperLimitContainer.clone();
            copy.upperLimitContainer.parent = copy;
            copy.domObj.append(copy.upperLimitContainer.domObj);
            copy.childContainers.push(copy.upperLimitContainer);
        }
        if (copy.hasLowerLimit) {
            copy.lowerLimitContainer = this.lowerLimitContainer.clone();
            copy.lowerLimitContainer.parent = copy;
            copy.domObj.append(copy.lowerLimitContainer.domObj);
            copy.childContainers.push(copy.lowerLimitContainer);
        }

        copy.symbol = new copy.integralSymbolCtors[copy.integralType](copy);
        copy.domObj.append(copy.symbol.domObj);
        copy.childNoncontainers = [copy.symbol];
        return copy;
    };
    eqEd.IntegralWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: this.integralType
        };
        if (!this.hasLowerLimit && !this.hasUpperLimit) {
            jsonObj.operands = null;
        } else if (this.hasLowerLimit && !this.hasUpperLimit) {
            jsonObj.operands = {
                lowerLimit: this.lowerLimitContainer.buildJsonObj()
            }
        } else if (!this.hasLowerLimit && this.hasUpperLimit) {
            jsonObj.operands = {
                upperLimit: this.upperLimitContainer.buildJsonObj()
            }
        } else {
            jsonObj.operands = {
                lowerLimit: this.lowerLimitContainer.buildJsonObj(),
                upperLimit: this.upperLimitContainer.buildJsonObj()
            }
        }
        return jsonObj;
    };

    eqEd.IntegralWrapper.constructFromJsonObj = function(jsonObj, equation) {
        var hasUpperLimit = (jsonObj.operands !== null && typeof jsonObj.operands.upperLimit !== "undefined");
        var hasLowerLimit = (jsonObj.operands !== null && typeof jsonObj.operands.lowerLimit !== "undefined");
        var integralWrapper = new eqEd.IntegralWrapper(equation, true, hasUpperLimit, hasLowerLimit, jsonObj.value);
        if (hasUpperLimit) {
            for (var i = 0; i < jsonObj.operands.upperLimit.length; i++) {
                var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.upperLimit[i].type);
                var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.upperLimit[i], equation);
                integralWrapper.upperLimitContainer.addWrappers([i, innerWrapper]);
            }
        }
        if (hasLowerLimit) {
            for (var i = 0; i < jsonObj.operands.lowerLimit.length; i++) {
                var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.lowerLimit[i].type);
                var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.lowerLimit[i], equation);
                integralWrapper.lowerLimitContainer.addWrappers([i, innerWrapper]);
            }
        }

        return integralWrapper;
    }
})();

/* End eq/js/equation-components/wrappers/integralWrapper.js*/

/* Begin eq/js/equation-components/misc/integralSymbol.js*/

eqEd.IntegralSymbol = function(parent) {
    eqEd.BigOperatorSymbol.call(this, parent); // call super constructor.
    this.className = "eqEd.IntegralSymbol";

    this.domObj = this.buildDomObj();

    // Height has already been added to properties in superclass
    // needs removed to be overriden
    for(var i = 0; i < this.properties.length; i++) {
        if (this.properties[i].propName === "height") {
            this.properties.splice(i, 1);
        }
    }

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 2.25 * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 0.40009004166 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.IntegralSymbol.prototype = Object.create(eqEd.BigOperatorSymbol.prototype);
    eqEd.IntegralSymbol.prototype.constructor = eqEd.IntegralSymbol;
    eqEd.IntegralSymbol.prototype.buildDomObj = function() {
        var htmlRep = '<div class="bigOperatorSymbol integralSymbol" style="width: 35.559544; height: 88.878853;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 35.559544 88.878853" preserveAspectRatio="none"><g transform="translate(-316.48568,-332.24859)"><g><path d="m 320.96562,416.28751 c -0.0167,-0.67086 -0.23334,-1.20918 -0.64999,-1.61498 -0.41667,-0.40586 -0.93333,-0.61419 -1.54998,-0.62499 -0.7425,0.0116 -1.30749,0.2383 -1.69498,0.67999 -0.3875,0.44163 -0.5825,1.02829 -0.58499,1.75997 0.0833,1.29579 0.52665,2.38411 1.32998,3.26496 0.80332,0.88079 1.82664,1.33912 3.06996,1.37498 0.94665,-0.025 1.7933,-0.28503 2.53997,-0.77999 0.74664,-0.49502 1.3533,-1.07502 1.81997,-1.73997 0.57999,-0.75836 1.13998,-1.70168 1.67998,-2.82997 0.53998,-1.12834 1.05998,-2.45166 1.55998,-3.96995 0.31248,-0.95334 0.63748,-2.03666 0.97499,-3.24995 0.33748,-1.21334 0.73247,-2.71666 1.18498,-4.50995 1.21577,-4.8797 2.32193,-9.52902 3.31848,-13.94796 0.99651,-4.41896 1.94859,-8.92902 2.85626,-13.5302 0.90762,-4.60116 1.836,-9.61492 2.78515,-15.04129 0.46413,-2.8216 0.8558,-5.08824 1.17498,-6.79991 0.31914,-1.71161 0.6608,-3.45825 1.02499,-5.23993 0.58663,-2.96576 1.10329,-5.34406 1.54998,-7.13491 0.44663,-1.79077 0.90329,-3.31908 1.36998,-4.58494 0.4233,-1.1666 0.85663,-2.09326 1.29999,-2.77997 0.44329,-0.6866 0.87662,-1.09326 1.29998,-1.21998 0.0733,-0.0333 0.16663,-0.0466 0.28,-0.04 0.20746,0.002 0.43246,0.0384 0.67499,0.11 0.24246,0.0717 0.45746,0.16838 0.64499,0.28999 0.13913,0.0984 0.30079,0.23172 0.48499,0.4 0.18413,0.16838 0.2758,0.26171 0.275,0.28 -0.61336,0.14505 -1.11669,0.38504 -1.50998,0.71999 -0.39336,0.33504 -0.59669,0.85504 -0.60999,1.55998 0.0166,0.67087 0.23329,1.20919 0.64999,1.61497 0.41663,0.40588 0.93329,0.61421 1.54998,0.625 0.75995,-0.0108 1.32994,-0.23912 1.70998,-0.68499 0.37995,-0.44578 0.56995,-1.04411 0.56999,-1.79498 -0.0884,-1.2941 -0.5317,-2.37575 -1.32998,-3.24496 -0.79836,-0.8691 -1.90168,-1.32076 -3.30996,-1.35498 -0.48169,0.008 -0.93835,0.10255 -1.36998,0.285 -0.4317,0.18255 -0.82836,0.40754 -1.18999,0.67499 -1.70834,1.37503 -3.09165,3.575 -4.14995,6.59991 -1.05834,3.02501 -1.98166,6.18496 -2.76996,9.47988 -1.22914,4.92836 -2.34419,9.6004 -3.34514,14.01612 -1.001,4.41576 -1.95308,8.92483 -2.85626,13.52723 -0.90322,4.60241 -1.82271,9.64777 -2.75848,15.1361 -0.56834,3.30495 -1.07167,6.13491 -1.50998,8.48989 -0.43834,2.35495 -0.88167,4.56492 -1.32999,6.62992 -0.73333,3.45909 -1.43666,6.16072 -2.10997,8.10489 -0.67333,1.94411 -1.35666,3.25576 -2.04997,3.93495 -0.21001,0.19746 -0.39001,0.33246 -0.54,0.40499 -0.15,0.0725 -0.33,0.0975 -0.53999,0.075 -0.34,0.002 -0.66,-0.0617 -0.95999,-0.18999 -0.3,-0.12837 -0.58,-0.3317 -0.83999,-0.61 -0.08,-0.0625 -0.14,-0.11753 -0.17999,-0.16499 -0.04,-0.0475 -0.06,-0.0725 -0.06,-0.075 0.61332,-0.14503 1.11664,-0.38503 1.50998,-0.71999 0.39332,-0.33503 0.59665,-0.85502 0.60999,-1.55998 z" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/integralSymbol.js*/

/* Begin eq/js/equation-components/misc/doubleIntegralSymbol.js*/

eqEd.DoubleIntegralSymbol = function(parent) {
    eqEd.BigOperatorSymbol.call(this, parent); // call super constructor.
    this.className = "eqEd.DoubleIntegralSymbol";

    this.domObj = this.buildDomObj();

    // Height has already been added to properties in superclass
    // needs removed to be overriden
    for(var i = 0; i < this.properties.length; i++) {
        if (this.properties[i].propName === "height") {
            this.properties.splice(i, 1);
        }
    }

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 2.25 * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 0.63771381028 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.DoubleIntegralSymbol.prototype = Object.create(eqEd.BigOperatorSymbol.prototype);
    eqEd.DoubleIntegralSymbol.prototype.constructor = eqEd.DoubleIntegralSymbol;
    eqEd.DoubleIntegralSymbol.prototype.buildDomObj = function() {
        var htmlRep = '<div class="bigOperatorSymbol doubleIntegralSymbol" style="width: 56.679272; height: 88.878853;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 56.679272 88.878853" preserveAspectRatio="none"><g transform="translate(-167.91427,-432.24859)"><g><path d="m 172.39421,516.28751 c -0.0167,-0.67086 -0.23334,-1.20918 -0.64999,-1.61498 -0.41667,-0.40586 -0.93333,-0.61419 -1.54998,-0.62499 -0.7425,0.0116 -1.30749,0.2383 -1.69498,0.67999 -0.3875,0.44163 -0.5825,1.02829 -0.58499,1.75997 0.0833,1.29579 0.52665,2.38411 1.32998,3.26496 0.80332,0.88079 1.82664,1.33912 3.06996,1.37498 0.94665,-0.025 1.7933,-0.28503 2.53997,-0.77999 0.74664,-0.49502 1.3533,-1.07502 1.81997,-1.73997 0.57998,-0.75836 1.13998,-1.70168 1.67998,-2.82997 0.53998,-1.12834 1.05997,-2.45166 1.55998,-3.96995 0.31248,-0.95334 0.63748,-2.03666 0.97499,-3.24995 0.33748,-1.21334 0.73247,-2.71666 1.18498,-4.50995 1.21577,-4.8797 2.32193,-9.52902 3.31848,-13.94796 0.99651,-4.41896 1.94859,-8.92902 2.85626,-13.5302 0.90762,-4.60116 1.836,-9.61492 2.78515,-15.04129 0.46413,-2.8216 0.85579,-5.08824 1.17498,-6.79991 0.31914,-1.71161 0.6608,-3.45825 1.02499,-5.23993 0.58663,-2.96576 1.10329,-5.34406 1.54998,-7.13491 0.44663,-1.79077 0.90329,-3.31908 1.36998,-4.58494 0.4233,-1.1666 0.85663,-2.09326 1.29999,-2.77997 0.44329,-0.6866 0.87662,-1.09326 1.29998,-1.21998 0.0733,-0.0333 0.16663,-0.0466 0.28,-0.04 0.20746,0.002 0.43246,0.0384 0.67499,0.11 0.24246,0.0717 0.45746,0.16838 0.64499,0.28999 0.13913,0.0984 0.30079,0.23172 0.48499,0.4 0.18413,0.16838 0.2758,0.26171 0.275,0.28 -0.61336,0.14505 -1.11669,0.38504 -1.50998,0.71999 -0.39336,0.33504 -0.59669,0.85504 -0.60999,1.55998 0.0166,0.67087 0.23329,1.20919 0.64999,1.61497 0.41662,0.40588 0.93328,0.61421 1.54998,0.625 0.75995,-0.0108 1.32994,-0.23912 1.70998,-0.68499 0.37995,-0.44578 0.56995,-1.04411 0.56999,-1.79498 -0.0884,-1.2941 -0.5317,-2.37575 -1.32999,-3.24496 -0.79835,-0.8691 -1.90167,-1.32076 -3.30995,-1.35498 -0.48169,0.008 -0.93836,0.10255 -1.36998,0.285 -0.4317,0.18255 -0.82836,0.40754 -1.18999,0.67499 -1.70834,1.37503 -3.09165,3.575 -4.14995,6.59991 -1.05834,3.02501 -1.98166,6.18496 -2.76996,9.47988 -1.22914,4.92836 -2.34419,9.6004 -3.34514,14.01612 -1.001,4.41576 -1.95308,8.92483 -2.85626,13.52723 -0.90322,4.60241 -1.82271,9.64777 -2.75848,15.1361 -0.56834,3.30495 -1.07167,6.13491 -1.50999,8.48989 -0.43834,2.35495 -0.88166,4.56492 -1.32998,6.62992 -0.73333,3.45909 -1.43666,6.16072 -2.10997,8.10489 -0.67333,1.94411 -1.35666,3.25576 -2.04997,3.93495 -0.21001,0.19746 -0.39001,0.33246 -0.54,0.40499 -0.15,0.0725 -0.33,0.0975 -0.53999,0.075 -0.34,0.002 -0.66,-0.0617 -0.95999,-0.18999 -0.3,-0.12837 -0.58,-0.3317 -0.83999,-0.61 -0.08,-0.0625 -0.14,-0.11753 -0.18,-0.16499 -0.04,-0.0475 -0.06,-0.0725 -0.06,-0.075 0.61332,-0.14503 1.11664,-0.38503 1.50998,-0.71999 0.39332,-0.33503 0.59665,-0.85502 0.60999,-1.55998 z m 21.11973,0 c -0.0167,-0.67086 -0.23336,-1.20918 -0.65,-1.61498 -0.41668,-0.40586 -0.93334,-0.61419 -1.54998,-0.62499 -0.74251,0.0116 -1.3075,0.2383 -1.69497,0.67999 -0.38752,0.44163 -0.58252,1.02829 -0.585,1.75997 0.0833,1.29579 0.52664,2.38411 1.32999,3.26496 0.80329,0.88079 1.82661,1.33912 3.06996,1.37498 0.94662,-0.025 1.79328,-0.28503 2.53996,-0.77999 0.74663,-0.49502 1.35329,-1.07502 1.81998,-1.73997 0.57996,-0.75836 1.13995,-1.70168 1.67998,-2.82997 0.53996,-1.12834 1.05995,-2.45166 1.55998,-3.96995 0.31246,-0.95334 0.63746,-2.03666 0.97499,-3.24995 0.33746,-1.21334 0.73245,-2.71666 1.18498,-4.50995 1.21575,-4.8797 2.32191,-9.52902 3.31848,-13.94796 0.99648,-4.41896 1.94857,-8.92902 2.85626,-13.5302 0.90759,-4.60116 1.83598,-9.61492 2.78515,-15.04129 0.46411,-2.8216 0.85577,-5.08824 1.17498,-6.79991 0.31912,-1.71161 0.66078,-3.45825 1.02499,-5.23993 0.58661,-2.96576 1.10327,-5.34406 1.54998,-7.13491 0.44661,-1.79077 0.90327,-3.31908 1.36998,-4.58494 0.42328,-1.1666 0.8566,-2.09326 1.29998,-2.77997 0.44328,-0.6866 0.87661,-1.09326 1.29999,-1.21998 0.0733,-0.0333 0.16661,-0.0466 0.27999,-0.04 0.20745,0.002 0.43244,0.0384 0.675,0.11 0.24244,0.0717 0.45743,0.16838 0.64499,0.28999 0.13911,0.0984 0.30077,0.23172 0.48499,0.4 0.18411,0.16838 0.27577,0.26171 0.275,0.28 -0.61338,0.14505 -1.11671,0.38504 -1.50998,0.71999 -0.39339,0.33504 -0.59672,0.85504 -0.61,1.55998 0.0166,0.67087 0.23328,1.20919 0.65,1.61497 0.4166,0.40588 0.93326,0.61421 1.54998,0.625 0.75993,-0.0108 1.32992,-0.23912 1.70997,-0.68499 0.37994,-0.44578 0.56994,-1.04411 0.57,-1.79498 -0.0884,-1.2941 -0.53172,-2.37575 -1.32999,-3.24496 -0.79838,-0.8691 -1.90169,-1.32076 -3.30995,-1.35498 -0.48172,0.008 -0.93838,0.10255 -1.36999,0.285 -0.43171,0.18255 -0.82837,0.40754 -1.18998,0.67499 -1.70836,1.37503 -3.09168,3.575 -4.14995,6.59991 -1.05836,3.02501 -1.98168,6.18496 -2.76996,9.47988 -1.22917,4.92836 -2.34421,9.6004 -3.34515,14.01612 -1.00101,4.41576 -1.9531,8.92483 -2.85625,13.52723 -0.90324,4.60241 -1.82273,9.64777 -2.75849,15.1361 -0.56836,3.30495 -1.07168,6.13491 -1.50998,8.48989 -0.43836,2.35495 -0.88169,4.56492 -1.32998,6.62992 -0.73336,3.45909 -1.43668,6.16072 -2.10997,8.10489 -0.67336,1.94411 -1.35668,3.25576 -2.04998,3.93495 -0.21002,0.19746 -0.39002,0.33246 -0.53999,0.40499 -0.15003,0.0725 -0.33002,0.0975 -0.53999,0.075 -0.34003,0.002 -0.66002,-0.0617 -0.95999,-0.18999 -0.30002,-0.12837 -0.58002,-0.3317 -0.83999,-0.61 -0.08,-0.0625 -0.14002,-0.11753 -0.18,-0.16499 -0.04,-0.0475 -0.06,-0.0725 -0.06,-0.075 0.6133,-0.14503 1.11663,-0.38503 1.50998,-0.71999 0.39331,-0.33503 0.59664,-0.85502 0.61,-1.55998 z" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/doubleIntegralSymbol.js*/

/* Begin eq/js/equation-components/misc/tripleIntegralSymbol.js*/

eqEd.TripleIntegralSymbol = function(parent) {
    eqEd.BigOperatorSymbol.call(this, parent); // call super constructor.
    this.className = "eqEd.TripleIntegralSymbol";

    this.domObj = this.buildDomObj();

    // Height has already been added to properties in superclass
    // needs removed to be overriden
    for(var i = 0; i < this.properties.length; i++) {
        if (this.properties[i].propName === "height") {
            this.properties.splice(i, 1);
        }
    }

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 2.25 * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 0.86633665265 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.TripleIntegralSymbol.prototype = Object.create(eqEd.BigOperatorSymbol.prototype);
    eqEd.TripleIntegralSymbol.prototype.constructor = eqEd.TripleIntegralSymbol;
    eqEd.TripleIntegralSymbol.prototype.buildDomObj = function() {
        var htmlRep = '<div class="bigOperatorSymbol tripleIntegralSymbol" style="width: 76.999008; height: 88.878853;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 76.999008 88.878853" preserveAspectRatio="none"><g transform="translate(-242.19997,-417.96288)"><g><path d="m 246.67991,502.0018 c -0.0167,-0.67085 -0.23333,-1.20918 -0.64999,-1.61498 -0.41666,-0.40586 -0.93332,-0.61419 -1.54998,-0.62499 -0.74249,0.0116 -1.30748,0.2383 -1.69498,0.67999 -0.38749,0.44163 -0.58249,1.02829 -0.58499,1.75998 0.0833,1.29578 0.52666,2.3841 1.32998,3.26496 0.80332,0.88078 1.82664,1.33911 3.06997,1.37498 0.94664,-0.025 1.7933,-0.28503 2.53996,-0.77999 0.74665,-0.49503 1.35331,-1.07502 1.81998,-1.73998 0.57998,-0.75835 1.13997,-1.70167 1.67998,-2.82996 0.53998,-1.12835 1.05997,-2.45166 1.55998,-3.96995 0.31248,-0.95335 0.63747,-2.03667 0.97498,-3.24996 0.33748,-1.21334 0.73248,-2.71665 1.18499,-4.50994 1.21577,-4.8797 2.32193,-9.52902 3.31847,-13.94797 0.99651,-4.41895 1.9486,-8.92902 2.85626,-13.5302 0.90762,-4.60116 1.83601,-9.61491 2.78515,-15.04128 0.46414,-2.82161 0.8558,-5.08824 1.17499,-6.79992 0.31913,-1.71161 0.6608,-3.45825 1.02499,-5.23993 0.58663,-2.96575 1.10329,-5.34406 1.54998,-7.13491 0.44663,-1.79076 0.90329,-3.31907 1.36998,-4.58494 0.42329,-1.1666 0.85662,-2.09325 1.29998,-2.77996 0.4433,-0.68661 0.87662,-1.09327 1.29998,-1.21999 0.0733,-0.0333 0.16664,-0.0466 0.28,-0.04 0.20746,0.002 0.43246,0.0384 0.67499,0.11 0.24247,0.0717 0.45746,0.16839 0.64499,0.29 0.13913,0.0984 0.3008,0.23172 0.485,0.39999 0.18413,0.16839 0.27579,0.26172 0.27499,0.28 -0.61336,0.14505 -1.11668,0.38505 -1.50998,0.71999 -0.39336,0.33505 -0.59669,0.85504 -0.60999,1.55998 0.0166,0.67087 0.2333,1.2092 0.64999,1.61498 0.41663,0.40587 0.93329,0.61421 1.54998,0.62499 0.75996,-0.0108 1.32995,-0.23912 1.70998,-0.68499 0.37996,-0.44578 0.56996,-1.0441 0.56999,-1.79498 -0.0884,-1.2941 -0.53169,-2.37575 -1.32998,-3.24495 -0.79836,-0.86911 -1.90168,-1.32077 -3.30996,-1.35499 -0.48169,0.008 -0.93835,0.10256 -1.36998,0.285 -0.43169,0.18255 -0.82835,0.40755 -1.18998,0.67499 -1.70834,1.37503 -3.09166,3.575 -4.14995,6.59992 -1.05835,3.025 -1.98167,6.18496 -2.76996,9.47987 -1.22915,4.92837 -2.34419,9.6004 -3.34515,14.01612 -1.00099,4.41576 -1.95308,8.92484 -2.85626,13.52723 -0.90321,4.60242 -1.8227,9.64778 -2.75848,15.1361 -0.56834,3.30495 -1.07167,6.13491 -1.50998,8.48989 -0.43834,2.35496 -0.88167,4.56493 -1.32998,6.62992 -0.73334,3.4591 -1.43666,6.16073 -2.10998,8.10489 -0.67333,1.94412 -1.35665,3.25576 -2.04997,3.93495 -0.21,0.19747 -0.39,0.33247 -0.53999,0.405 -0.15001,0.0725 -0.33,0.0975 -0.53999,0.075 -0.34001,0.002 -0.66,-0.0617 -0.95999,-0.19 -0.3,-0.12836 -0.58,-0.33169 -0.83999,-0.60999 -0.08,-0.0625 -0.14,-0.11753 -0.18,-0.165 -0.04,-0.0475 -0.06,-0.0725 -0.06,-0.075 0.61332,-0.14503 1.11665,-0.38502 1.50998,-0.71999 0.39332,-0.33503 0.59665,-0.85502 0.60999,-1.55998 z m 21.11973,0 c -0.0167,-0.67085 -0.23336,-1.20918 -0.64999,-1.61498 -0.41669,-0.40586 -0.93335,-0.61419 -1.54998,-0.62499 -0.74252,0.0116 -1.30751,0.2383 -1.69498,0.67999 -0.38752,0.44163 -0.58251,1.02829 -0.58499,1.75998 0.0833,1.29578 0.52664,2.3841 1.32998,3.26496 0.8033,0.88078 1.82662,1.33911 3.06996,1.37498 0.94663,-0.025 1.79328,-0.28503 2.53997,-0.77999 0.74663,-0.49503 1.35328,-1.07502 1.81998,-1.73998 0.57996,-0.75835 1.13995,-1.70167 1.67998,-2.82996 0.53995,-1.12835 1.05995,-2.45166 1.55998,-3.96995 0.31246,-0.95335 0.63745,-2.03667 0.97498,-3.24996 0.33746,-1.21334 0.73246,-2.71665 1.18499,-4.50994 1.21575,-4.8797 2.3219,-9.52902 3.31847,-13.94797 0.99649,-4.41895 1.94858,-8.92902 2.85626,-13.5302 0.9076,-4.60116 1.83598,-9.61491 2.78515,-15.04128 0.46412,-2.82161 0.85578,-5.08824 1.17499,-6.79992 0.31911,-1.71161 0.66077,-3.45825 1.02498,-5.23993 0.58661,-2.96575 1.10327,-5.34406 1.54998,-7.13491 0.44661,-1.79076 0.90327,-3.31907 1.36999,-4.58494 0.42327,-1.1666 0.8566,-2.09325 1.29998,-2.77996 0.44327,-0.68661 0.8766,-1.09327 1.29998,-1.21999 0.0733,-0.0333 0.16661,-0.0466 0.28,-0.04 0.20744,0.002 0.43244,0.0384 0.67499,0.11 0.24244,0.0717 0.45744,0.16839 0.64499,0.29 0.13911,0.0984 0.30078,0.23172 0.485,0.39999 0.1841,0.16839 0.27577,0.26172 0.27499,0.28 -0.61338,0.14505 -1.11671,0.38505 -1.50998,0.71999 -0.39338,0.33505 -0.59671,0.85504 -0.60999,1.55998 0.0166,0.67087 0.23328,1.2092 0.64999,1.61498 0.41661,0.40587 0.93327,0.61421 1.54998,0.62499 0.75993,-0.0108 1.32993,-0.23912 1.70998,-0.68499 0.37994,-0.44578 0.56993,-1.0441 0.56999,-1.79498 -0.0884,-1.2941 -0.53172,-2.37575 -1.32998,-3.24495 -0.79838,-0.86911 -1.9017,-1.32077 -3.30996,-1.35499 -0.48171,0.008 -0.93837,0.10256 -1.36998,0.285 -0.43172,0.18255 -0.82838,0.40755 -1.18999,0.67499 -1.70836,1.37503 -3.09167,3.575 -4.14994,6.59992 -1.05837,3.025 -1.98169,6.18496 -2.76997,9.47987 -1.22916,4.92837 -2.34421,9.6004 -3.34514,14.01612 -1.00101,4.41576 -1.9531,8.92484 -2.85626,13.52723 -0.90323,4.60242 -1.82273,9.64778 -2.75848,15.1361 -0.56836,3.30495 -1.07169,6.13491 -1.50998,8.48989 -0.43836,2.35496 -0.88169,4.56493 -1.32998,6.62992 -0.73336,3.4591 -1.43668,6.16073 -2.10998,8.10489 -0.67335,1.94412 -1.35668,3.25576 -2.04997,3.93495 -0.21003,0.19747 -0.39003,0.33247 -0.53999,0.405 -0.15003,0.0725 -0.33003,0.0975 -0.54,0.075 -0.34002,0.002 -0.66002,-0.0617 -0.95998,-0.19 -0.30003,-0.12836 -0.58002,-0.33169 -0.83999,-0.60999 -0.08,-0.0625 -0.14003,-0.11753 -0.18,-0.165 -0.04,-0.0475 -0.06,-0.0725 -0.06,-0.075 0.6133,-0.14503 1.11662,-0.38502 1.50998,-0.71999 0.3933,-0.33503 0.59663,-0.85502 0.60999,-1.55998 z m 20.31974,0 c -0.0167,-0.67085 -0.23338,-1.20918 -0.64999,-1.61498 -0.41671,-0.40586 -0.93337,-0.61419 -1.54998,-0.62499 -0.74254,0.0116 -1.30753,0.2383 -1.69498,0.67999 -0.38754,0.44163 -0.58254,1.02829 -0.58499,1.75998 0.0833,1.29578 0.52661,2.3841 1.32998,3.26496 0.80328,0.88078 1.8266,1.33911 3.06996,1.37498 0.94661,-0.025 1.79326,-0.28503 2.53997,-0.77999 0.7466,-0.49503 1.35326,-1.07502 1.81998,-1.73998 0.57993,-0.75835 1.13993,-1.70167 1.67997,-2.82996 0.53994,-1.12835 1.05994,-2.45166 1.55998,-3.96995 0.31244,-0.95335 0.63744,-2.03667 0.97499,-3.24996 0.33744,-1.21334 0.73243,-2.71665 1.18499,-4.50994 1.21572,-4.8797 2.32188,-9.52902 3.31847,-13.94797 0.99647,-4.41895 1.94855,-8.92902 2.85626,-13.5302 0.90758,-4.60116 1.83596,-9.61491 2.78515,-15.04128 0.46409,-2.82161 0.85575,-5.08824 1.17499,-6.79992 0.31909,-1.71161 0.66075,-3.45825 1.02498,-5.23993 0.58659,-2.96575 1.10325,-5.34406 1.54998,-7.13491 0.44659,-1.79076 0.90325,-3.31907 1.36998,-4.58494 0.42326,-1.1666 0.85659,-2.09325 1.29999,-2.77996 0.44325,-0.68661 0.87658,-1.09327 1.29998,-1.21999 0.0733,-0.0333 0.16659,-0.0466 0.28,-0.04 0.20742,0.002 0.43242,0.0384 0.67499,0.11 0.24242,0.0717 0.45742,0.16839 0.64499,0.29 0.13909,0.0984 0.30075,0.23172 0.48499,0.39999 0.18409,0.16839 0.27576,0.26172 0.275,0.28 -0.6134,0.14505 -1.11673,0.38505 -1.50998,0.71999 -0.3934,0.33505 -0.59673,0.85504 -0.60999,1.55998 0.0166,0.67087 0.23325,1.2092 0.64999,1.61498 0.41659,0.40587 0.93324,0.61421 1.54998,0.62499 0.75991,-0.0108 1.3299,-0.23912 1.70998,-0.68499 0.37991,-0.44578 0.56991,-1.0441 0.56999,-1.79498 -0.0884,-1.2941 -0.53174,-2.37575 -1.32998,-3.24495 -0.7984,-0.86911 -1.90172,-1.32077 -3.30996,-1.35499 -0.48173,0.008 -0.93839,0.10256 -1.36998,0.285 -0.43174,0.18255 -0.8284,0.40755 -1.18999,0.67499 -1.70838,1.37503 -3.09169,3.575 -4.14994,6.59992 -1.05839,3.025 -1.98171,6.18496 -2.76997,9.47987 -1.22918,4.92837 -2.34423,9.6004 -3.34514,14.01612 -1.00104,4.41576 -1.95312,8.92484 -2.85626,13.52723 -0.90326,4.60242 -1.82275,9.64778 -2.75848,15.1361 -0.56838,3.30495 -1.07171,6.13491 -1.50998,8.48989 -0.43838,2.35496 -0.88171,4.56493 -1.32999,6.62992 -0.73337,3.4591 -1.4367,6.16073 -2.10997,8.10489 -0.67337,1.94412 -1.3567,3.25576 -2.04997,3.93495 -0.21005,0.19747 -0.39005,0.33247 -0.54,0.405 -0.15004,0.0725 -0.33004,0.0975 -0.53999,0.075 -0.34004,0.002 -0.66004,-0.0617 -0.95999,-0.19 -0.30004,-0.12836 -0.58004,-0.33169 -0.83999,-0.60999 -0.08,-0.0625 -0.14004,-0.11753 -0.17999,-0.165 -0.0401,-0.0475 -0.06,-0.0725 -0.06,-0.075 0.61328,-0.14503 1.1166,-0.38502 1.50998,-0.71999 0.39328,-0.33503 0.59661,-0.85502 0.60999,-1.55998 z" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/tripleIntegralSymbol.js*/

/* Begin eq/js/equation-components/misc/contourIntegralSymbol.js*/

eqEd.ContourIntegralSymbol = function(parent) {
    eqEd.BigOperatorSymbol.call(this, parent); // call super constructor.
    this.className = "eqEd.ContourIntegralSymbol";

    this.domObj = this.buildDomObj();

    // Height has already been added to properties in superclass
    // needs removed to be overriden
    for(var i = 0; i < this.properties.length; i++) {
        if (this.properties[i].propName === "height") {
            this.properties.splice(i, 1);
        }
    }

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 2.25 * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 0.40004502251 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.ContourIntegralSymbol.prototype = Object.create(eqEd.BigOperatorSymbol.prototype);
    eqEd.ContourIntegralSymbol.prototype.constructor = eqEd.ContourIntegralSymbol;
    eqEd.ContourIntegralSymbol.prototype.buildDomObj = function() {
        var htmlRep = '<div class="bigOperatorSymbol contourIntegralSymbol" style="width: 35.559544; height: 88.888855;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 35.559544 88.888855" preserveAspectRatio="none"><g transform="translate(-276.48568,-397.95288)"><g><path d="m 280.96562,482.0018 c -0.0167,-0.67085 -0.23334,-1.20918 -0.64999,-1.61498 -0.41667,-0.40586 -0.93333,-0.61419 -1.54998,-0.62499 -0.7425,0.0116 -1.30749,0.2383 -1.69498,0.67999 -0.3875,0.44163 -0.5825,1.02829 -0.58499,1.75998 0.0833,1.29578 0.52665,2.3841 1.32998,3.26496 0.80332,0.88078 1.82664,1.33911 3.06996,1.37498 0.94665,-0.025 1.7933,-0.28503 2.53997,-0.77999 0.74664,-0.49503 1.3533,-1.07502 1.81997,-1.73998 1.35997,-1.79334 2.63996,-4.68663 3.83996,-8.67989 0.34565,-1.19753 0.8054,-2.93134 1.37924,-5.20141 0.5738,-2.27011 1.18762,-4.77131 1.84145,-7.50361 0.6538,-2.73232 1.27355,-5.39055 1.85924,-7.97471 l 0.35999,-1.75998 0.4,0 c 1.83912,-0.18 3.48076,-0.69999 4.92494,-1.55998 1.44412,-0.85999 2.57577,-1.93997 3.39495,-3.23996 0.58413,-0.81998 1.03579,-1.69997 1.35498,-2.63996 0.31914,-0.93998 0.4808,-2.05997 0.485,-3.35996 -8.6e-4,-0.28499 -0.009,-0.55498 -0.025,-0.80999 -0.0159,-0.25499 -0.0342,-0.46498 -0.055,-0.62999 -0.31669,-1.90996 -0.98335,-3.55994 -1.99997,-4.94994 -1.01669,-1.38996 -2.28333,-2.45995 -3.79996,-3.20996 -0.14419,-0.0816 -0.26585,-0.14831 -0.36499,-0.19999 -0.0992,-0.0516 -0.15086,-0.0783 -0.155,-0.08 l 0.79999,-4.59994 c 0.53664,-3.21243 0.9633,-5.6874 1.27998,-7.42491 0.31664,-1.73744 0.6633,-3.50242 1.03999,-5.29493 0.58663,-2.96575 1.10329,-5.34406 1.54998,-7.13491 0.44663,-1.79076 0.90329,-3.31907 1.36998,-4.58494 0.4233,-1.1666 0.85663,-2.09325 1.29999,-2.77996 0.44329,-0.68661 0.87662,-1.09327 1.29998,-1.21999 0.0733,-0.0333 0.16663,-0.0466 0.28,-0.04 0.20746,0.002 0.43246,0.0384 0.67499,0.11 0.24246,0.0717 0.45746,0.16839 0.64499,0.29 0.13913,0.0984 0.30079,0.23172 0.48499,0.39999 0.18413,0.16839 0.2758,0.26172 0.275,0.28 -0.61336,0.14505 -1.11669,0.38505 -1.50998,0.71999 -0.39336,0.33505 -0.59669,0.85504 -0.60999,1.55998 0.0166,0.67087 0.23329,1.2092 0.64999,1.61498 0.41663,0.40587 0.93329,0.61421 1.54998,0.62499 0.75995,-0.0108 1.32994,-0.23912 1.70998,-0.68499 0.37995,-0.44578 0.56995,-1.0441 0.56999,-1.79498 -0.0884,-1.2941 -0.5317,-2.37575 -1.32998,-3.24495 -0.79836,-0.86911 -1.90168,-1.32077 -3.30996,-1.35499 -0.1667,-0.007 -0.31336,0.007 -0.44,0.04 -0.77752,0.15172 -1.46251,0.44838 -2.05497,0.88999 -0.59252,0.44172 -1.10751,0.95837 -1.54498,1.54998 -0.69752,0.89587 -1.36251,2.05419 -1.99497,3.47496 -0.63252,1.42086 -1.24751,3.12917 -1.84498,5.12493 -0.34619,1.1346 -0.8094,2.84321 -1.38961,5.12586 -0.58026,2.28272 -1.2005,4.81207 -1.86072,7.58805 -0.66026,2.77604 -1.28346,5.47131 -1.8696,8.08582 l -0.36,1.75998 -0.39999,0 c -1.83666,0.18085 -3.47331,0.69918 -4.90994,1.55498 -1.43666,0.85584 -2.57331,1.92416 -3.40996,3.20496 -0.58417,0.85667 -1.03583,1.75332 -1.35498,2.68996 -0.31917,0.93667 -0.48084,2.05332 -0.48499,3.34996 0.004,1.29999 0.16582,2.41998 0.48499,3.35996 0.31915,0.93999 0.77081,1.81998 1.35498,2.63996 0.56498,0.87416 1.23497,1.62582 2.00998,2.25497 0.77497,0.62916 1.62496,1.17082 2.54996,1.62498 -0.35531,2.22071 -0.76025,4.6192 -1.21479,7.19547 -0.45458,2.57625 -0.90692,5.04585 -1.35702,7.40879 -0.45013,2.36292 -0.84618,4.33474 -1.18814,5.91548 -0.73333,3.4591 -1.43666,6.16073 -2.10997,8.10489 -0.67333,1.94412 -1.35666,3.25576 -2.04997,3.93495 -0.21001,0.19747 -0.39001,0.33247 -0.54,0.405 -0.15,0.0725 -0.33,0.0975 -0.53999,0.075 -0.34,0.002 -0.66,-0.0617 -0.95999,-0.19 -0.3,-0.12836 -0.58,-0.33169 -0.83999,-0.60999 -0.08,-0.0625 -0.14,-0.11753 -0.17999,-0.165 -0.04,-0.0475 -0.06,-0.0725 -0.06,-0.075 0.61332,-0.14503 1.11664,-0.38502 1.50998,-0.71999 0.39332,-0.33503 0.59665,-0.85502 0.60999,-1.55998 z m 10.55986,-38.5595 c -0.17918,0.97166 -0.40084,2.14831 -0.66499,3.52995 -0.26418,1.38165 -0.45584,2.41831 -0.57499,3.10996 -0.02,0.16334 -0.04,0.29667 -0.06,0.4 -0.02,0.10333 -0.04,0.15666 -0.06,0.15999 l -0.87999,-0.51999 c -0.98833,-0.64749 -1.82165,-1.44248 -2.49997,-2.38497 -0.67833,-0.94248 -1.15166,-1.98747 -1.41998,-3.13496 -0.0975,-0.36665 -0.16251,-0.71331 -0.19499,-1.03998 -0.0325,-0.32666 -0.0475,-0.71332 -0.045,-1.15999 -0.003,-0.44665 0.0125,-0.83331 0.045,-1.15998 0.0325,-0.32665 0.0975,-0.67332 0.19499,-1.03999 0.32999,-1.3333 0.91998,-2.52662 1.76998,-3.57996 0.84998,-1.0533 1.89996,-1.88662 3.14996,-2.49996 0.50081,-0.24665 1.04914,-0.45331 1.64498,-0.62 0.59581,-0.16664 1.11413,-0.25331 1.55498,-0.25999 l 0.2,0 c -0.0645,0.21927 -0.22446,0.94075 -0.48,2.16442 -0.25557,1.2237 -0.54001,2.59405 -0.85332,4.11105 -0.31335,1.51703 -0.5889,2.82516 -0.82666,3.9244 z m 11.95985,-1.03999 c -8.6e-4,0.26251 -0.009,0.5075 -0.025,0.73499 -0.0159,0.22751 -0.0342,0.4225 -0.055,0.58499 -0.36586,1.96165 -1.16418,3.60829 -2.39497,4.93994 -1.23084,1.33165 -2.73915,2.21831 -4.52494,2.65997 -0.25502,0.0625 -0.52502,0.1175 -0.80999,0.16499 -0.28502,0.0475 -0.49502,0.0725 -0.62999,0.075 l -0.16,0 c 0.0644,-0.21901 0.22442,-0.94097 0.47999,-2.1659 0.25553,-1.22491 0.53998,-2.59872 0.85333,-4.12142 0.3133,-1.52269 0.58886,-2.8402 0.82665,-3.95255 0.19164,-1.0983 0.41831,-2.36162 0.67999,-3.78995 0.26164,-1.4283 0.48831,-2.55161 0.68,-3.36995 1.41912,0.76834 2.61077,1.82166 3.57495,3.15996 0.96412,1.33833 1.46578,3.03164 1.50498,5.07993 z" /></g></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/contourIntegralSymbol.js*/

/* Begin eq/js/equation-components/misc/contourDoubleIntegralSymbol.js*/

eqEd.ContourDoubleIntegralSymbol = function(parent) {
    eqEd.BigOperatorSymbol.call(this, parent); // call super constructor.
    this.className = "eqEd.ContourDoubleIntegralSymbol";

    this.domObj = this.buildDomObj();

    // Height has already been added to properties in superclass
    // needs removed to be overriden
    for(var i = 0; i < this.properties.length; i++) {
        if (this.properties[i].propName === "height") {
            this.properties.splice(i, 1);
        }
    }

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 2.25 * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 0.63771381028 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.ContourDoubleIntegralSymbol.prototype = Object.create(eqEd.BigOperatorSymbol.prototype);
    eqEd.ContourDoubleIntegralSymbol.prototype.constructor = eqEd.ContourDoubleIntegralSymbol;
    eqEd.ContourDoubleIntegralSymbol.prototype.buildDomObj = function() {
        var htmlRep = '<div class="bigOperatorSymbol contourDoubleIntegralSymbol" style="width: 56.679272; height: 88.878853;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 56.679272 88.878853" preserveAspectRatio="none"><g transform="translate(-195.13912,-222.1657)"><g><path d="m 199.61906,306.20462 c -0.0167,-0.67085 -0.23334,-1.20918 -0.64999,-1.61498 -0.41667,-0.40586 -0.93333,-0.61419 -1.54998,-0.62499 -0.74249,0.0116 -1.30749,0.2383 -1.69498,0.67999 -0.3875,0.44163 -0.58249,1.02829 -0.58499,1.75998 0.0833,1.29578 0.52666,2.3841 1.32998,3.26496 0.80332,0.88078 1.82664,1.33911 3.06996,1.37498 0.94665,-0.025 1.7933,-0.28503 2.53997,-0.77999 0.74665,-0.49503 1.3533,-1.07502 1.81998,-1.73998 0.57998,-0.75835 1.13997,-1.70167 1.67998,-2.82996 0.53998,-1.12835 1.05997,-2.45167 1.55998,-3.96995 0.31248,-0.95335 0.63747,-2.03667 0.97498,-3.24996 0.33748,-1.21334 0.73248,-2.71665 1.18499,-4.50994 1.21577,-4.8797 2.32192,-9.52902 3.31847,-13.94797 0.99651,-4.41895 1.9486,-8.92902 2.85626,-13.5302 0.90762,-4.60116 1.836,-9.61491 2.78515,-15.04128 0.46414,-2.82161 0.8558,-5.08824 1.17499,-6.79992 0.31913,-1.71161 0.66079,-3.45825 1.02498,-5.23993 0.58663,-2.96576 1.10329,-5.34406 1.54998,-7.13491 0.44664,-1.79076 0.9033,-3.31907 1.36999,-4.58494 0.42329,-1.1666 0.85662,-2.09325 1.29998,-2.77996 0.44329,-0.68661 0.87662,-1.09327 1.29998,-1.21999 0.0733,-0.0333 0.16663,-0.0466 0.28,-0.04 0.20746,0.002 0.43246,0.0384 0.67499,0.11 0.24246,0.0717 0.45746,0.16839 0.64499,0.29 0.13913,0.0984 0.3008,0.23172 0.485,0.39999 0.18412,0.16839 0.27579,0.26172 0.27499,0.28 -0.61336,0.14505 -1.11668,0.38505 -1.50998,0.71999 -0.39336,0.33505 -0.59669,0.85504 -0.60999,1.55998 0.0166,0.67087 0.2333,1.2092 0.64999,1.61498 0.41663,0.40587 0.93329,0.61421 1.54998,0.62499 0.75996,-0.0108 1.32995,-0.23912 1.70998,-0.68499 0.37996,-0.44578 0.56995,-1.0441 0.56999,-1.79498 -0.0884,-1.2941 -0.5317,-2.37575 -1.32998,-3.24495 -0.79836,-0.86911 -1.90168,-1.32077 -3.30996,-1.35499 -0.48169,0.008 -0.93835,0.10256 -1.36998,0.285 -0.43169,0.18255 -0.82836,0.40755 -1.18999,0.67499 -1.70834,1.37503 -3.09165,3.575 -4.14994,6.59992 -1.05835,3.025 -1.98167,6.18496 -2.76997,9.47987 -1.22914,4.92837 -2.34419,9.6004 -3.34514,14.01612 -1.00099,4.41576 -1.95308,8.92484 -2.85626,13.52723 -0.90321,4.60242 -1.82271,9.64778 -2.75848,15.1361 -0.56834,3.30495 -1.07167,6.13491 -1.50998,8.48989 -0.43834,2.35496 -0.88167,4.56493 -1.32998,6.62992 -0.73334,3.4591 -1.43666,6.16073 -2.10998,8.10489 -0.67333,1.94412 -1.35665,3.25576 -2.04997,3.93495 -0.21001,0.19747 -0.39,0.33247 -0.53999,0.405 -0.15001,0.0725 -0.33001,0.0975 -0.54,0.075 -0.34,0.002 -0.65999,-0.0617 -0.95998,-0.19 -0.30001,-0.12836 -0.58,-0.33169 -0.83999,-0.60999 -0.08,-0.0625 -0.14001,-0.11753 -0.18,-0.165 -0.04,-0.0475 -0.06,-0.0725 -0.06,-0.075 0.61332,-0.14503 1.11665,-0.38502 1.50998,-0.71999 0.39332,-0.33503 0.59665,-0.85502 0.60999,-1.55998 z m 21.11973,0 c -0.0167,-0.67085 -0.23336,-1.20918 -0.64999,-1.61498 -0.41669,-0.40586 -0.93335,-0.61419 -1.54998,-0.62499 -0.74252,0.0116 -1.30751,0.2383 -1.69498,0.67999 -0.38752,0.44163 -0.58252,1.02829 -0.58499,1.75998 0.0833,1.29578 0.52663,2.3841 1.32998,3.26496 0.8033,0.88078 1.82662,1.33911 3.06996,1.37498 0.94663,-0.025 1.79328,-0.28503 2.53997,-0.77999 0.74662,-0.49503 1.35328,-1.07502 1.81997,-1.73998 0.57996,-0.75835 1.13996,-1.70167 1.67998,-2.82996 0.53996,-1.12835 1.05996,-2.45167 1.55998,-3.96995 0.31246,-0.95335 0.63746,-2.03667 0.97499,-3.24996 0.33746,-1.21334 0.73245,-2.71665 1.18499,-4.50994 1.21574,-4.8797 2.3219,-9.52902 3.31847,-13.94797 0.99649,-4.41895 1.94857,-8.92902 2.85626,-13.5302 0.9076,-4.60116 1.83598,-9.61491 2.78515,-15.04128 0.46411,-2.82161 0.85577,-5.08824 1.17498,-6.79992 0.31912,-1.71161 0.66078,-3.45825 1.02499,-5.23993 0.58661,-2.96576 1.10327,-5.34406 1.54998,-7.13491 0.44661,-1.79076 0.90327,-3.31907 1.36998,-4.58494 0.42328,-1.1666 0.85661,-2.09325 1.29999,-2.77996 0.44327,-0.68661 0.8766,-1.09327 1.29998,-1.21999 0.0733,-0.0333 0.16661,-0.0466 0.28,-0.04 0.20744,0.002 0.43244,0.0384 0.67499,0.11 0.24244,0.0717 0.45744,0.16839 0.64499,0.29 0.13911,0.0984 0.30077,0.23172 0.48499,0.39999 0.18411,0.16839 0.27578,0.26172 0.275,0.28 -0.61338,0.14505 -1.11671,0.38505 -1.50998,0.71999 -0.39338,0.33505 -0.59671,0.85504 -0.60999,1.55998 0.0166,0.67087 0.23327,1.2092 0.64999,1.61498 0.41661,0.40587 0.93326,0.61421 1.54998,0.62499 0.75993,-0.0108 1.32992,-0.23912 1.70998,-0.68499 0.37993,-0.44578 0.56993,-1.0441 0.56999,-1.79498 -0.0884,-1.2941 -0.53172,-2.37575 -1.32998,-3.24495 -0.79838,-0.86911 -1.9017,-1.32077 -3.30996,-1.35499 -0.48171,0.008 -0.93838,0.10256 -1.36998,0.285 -0.43172,0.18255 -0.82838,0.40755 -1.18999,0.67499 -1.70836,1.37503 -3.09167,3.575 -4.14994,6.59992 -1.05837,3.025 -1.98169,6.18496 -2.76997,9.47987 -1.22916,4.92837 -2.34421,9.6004 -3.34514,14.01612 -1.00102,4.41576 -1.9531,8.92484 -2.85626,13.52723 -0.90324,4.60242 -1.82273,9.64778 -2.75848,15.1361 -0.56836,3.30495 -1.07169,6.13491 -1.50998,8.48989 -0.43836,2.35496 -0.88169,4.56493 -1.32999,6.62992 -0.73335,3.4591 -1.43668,6.16073 -2.10997,8.10489 -0.67335,1.94412 -1.35668,3.25576 -2.04997,3.93495 -0.21003,0.19747 -0.39003,0.33247 -0.54,0.405 -0.15002,0.0725 -0.33002,0.0975 -0.53999,0.075 -0.34002,0.002 -0.66002,-0.0617 -0.95999,-0.19 -0.30002,-0.12836 -0.58002,-0.33169 -0.83999,-0.60999 -0.08,-0.0625 -0.14002,-0.11753 -0.17999,-0.165 -0.04,-0.0475 -0.06,-0.0725 -0.06,-0.075 0.6133,-0.14503 1.11662,-0.38502 1.50998,-0.71999 0.3933,-0.33503 0.59663,-0.85502 0.60999,-1.55998 z" /></g><rect style="fill:none;stroke:#000000;stroke-width:2.20000005;stroke-linecap:round;stroke-linejoin:miter;stroke-miterlimit:4;stroke-opacity:1;stroke-dasharray:none;stroke-dashoffset:0" width="45.400002" height="25.085001" x="200.73912" y="254.06256" ry="13.392859" rx="32.856998" /></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/contourDoubleIntegralSymbol.js*/

/* Begin eq/js/equation-components/misc/contourTripleIntegralSymbol.js*/

eqEd.ContourTripleIntegralSymbol = function(parent) {
    eqEd.BigOperatorSymbol.call(this, parent); // call super constructor.
    this.className = "eqEd.ContourTripleIntegralSymbol";

    this.domObj = this.buildDomObj();

    // Height has already been added to properties in superclass
    // needs removed to be overriden
    for(var i = 0; i < this.properties.length; i++) {
        if (this.properties[i].propName === "height") {
            this.properties.splice(i, 1);
        }
    }

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 2.25 * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return 0.86633658442 * this.height;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.ContourTripleIntegralSymbol.prototype = Object.create(eqEd.BigOperatorSymbol.prototype);
    eqEd.ContourTripleIntegralSymbol.prototype.constructor = eqEd.ContourTripleIntegralSymbol;
    eqEd.ContourTripleIntegralSymbol.prototype.buildDomObj = function() {
        var htmlRep = '<div class="bigOperatorSymbol contourTripleIntegralSymbol" style="width: 76.999008; height: 88.87886;"><svg style="position: absolute; width: 100%; height: 100%;" viewBox="0 0 76.999008 88.87886" preserveAspectRatio="none"><g transform="translate(-304.7997,-370.40157)"><g><path d="m 309.27965,454.44049 c -0.0167,-0.67085 -0.23334,-1.20918 -0.65,-1.61498 -0.41666,-0.40586 -0.93332,-0.61419 -1.54998,-0.62499 -0.74249,0.0116 -1.30748,0.2383 -1.69498,0.67999 -0.38749,0.44163 -0.58249,1.02829 -0.58499,1.75998 0.0833,1.29578 0.52666,2.3841 1.32999,3.26496 0.80331,0.88078 1.82663,1.33911 3.06996,1.37498 0.94664,-0.025 1.7933,-0.28503 2.53996,-0.77999 0.74665,-0.49503 1.35331,-1.07502 1.81998,-1.73998 0.57998,-0.75835 1.13997,-1.70167 1.67998,-2.82996 0.53998,-1.12835 1.05997,-2.45166 1.55998,-3.96995 0.31248,-0.95335 0.63748,-2.03667 0.97499,-3.24996 0.33748,-1.21334 0.73247,-2.71665 1.18498,-4.50994 1.21577,-4.8797 2.32193,-9.52902 3.31848,-13.94797 0.9965,-4.41895 1.94859,-8.92902 2.85625,-13.5302 0.90762,-4.60116 1.83601,-9.61491 2.78516,-15.04128 0.46413,-2.82161 0.85579,-5.08824 1.17498,-6.79992 0.31914,-1.71161 0.6608,-3.45825 1.02499,-5.23993 0.58663,-2.96575 1.10329,-5.34406 1.54998,-7.13491 0.44663,-1.79076 0.90329,-3.31907 1.36998,-4.58494 0.4233,-1.1666 0.85662,-2.09325 1.29998,-2.77996 0.4433,-0.68661 0.87663,-1.09327 1.29999,-1.21999 0.0733,-0.0333 0.16663,-0.0466 0.27999,-0.04 0.20747,0.002 0.43246,0.0384 0.67499,0.11 0.24247,0.0717 0.45746,0.16839 0.645,0.29 0.13913,0.0984 0.30079,0.23172 0.48499,0.39999 0.18413,0.16839 0.27579,0.26172 0.275,0.28 -0.61336,0.14505 -1.11669,0.38505 -1.50999,0.71999 -0.39336,0.33505 -0.59669,0.85504 -0.60999,1.55998 0.0166,0.67087 0.2333,1.2092 0.64999,1.61498 0.41663,0.40587 0.93329,0.61421 1.54998,0.62499 0.75996,-0.0108 1.32995,-0.23911 1.70998,-0.68499 0.37996,-0.44578 0.56996,-1.0441 0.57,-1.79498 -0.0884,-1.2941 -0.5317,-2.37575 -1.32999,-3.24495 -0.79836,-0.86911 -1.90167,-1.32077 -3.30995,-1.35499 -0.4817,0.008 -0.93836,0.10256 -1.36999,0.285 -0.43169,0.18255 -0.82835,0.40755 -1.18998,0.67499 -1.70834,1.37504 -3.09166,3.575 -4.14995,6.59992 -1.05834,3.025 -1.98166,6.18496 -2.76996,9.47987 -1.22915,4.92837 -2.34419,9.6004 -3.34515,14.01612 -1.00099,4.41576 -1.95308,8.92484 -2.85626,13.52723 -0.90321,4.60242 -1.8227,9.64778 -2.75848,15.1361 -0.56834,3.30495 -1.07166,6.13491 -1.50998,8.4899 -0.43834,2.35495 -0.88167,4.56492 -1.32998,6.62991 -0.73334,3.4591 -1.43666,6.16073 -2.10997,8.10489 -0.67334,1.94412 -1.35666,3.25576 -2.04998,3.93495 -0.21,0.19747 -0.39,0.33247 -0.53999,0.405 -0.15001,0.0725 -0.33,0.0975 -0.53999,0.075 -0.34001,0.002 -0.66,-0.0617 -0.95999,-0.19 -0.3,-0.12836 -0.58,-0.33169 -0.83999,-0.60999 -0.08,-0.0625 -0.14,-0.11753 -0.18,-0.165 -0.04,-0.0475 -0.06,-0.0725 -0.06,-0.075 0.61332,-0.14503 1.11665,-0.38502 1.50998,-0.71999 0.39333,-0.33503 0.59666,-0.85502 0.61,-1.55998 z m 21.11972,0 c -0.0167,-0.67085 -0.23336,-1.20918 -0.64999,-1.61498 -0.41669,-0.40586 -0.93335,-0.61419 -1.54998,-0.62499 -0.74251,0.0116 -1.30751,0.2383 -1.69498,0.67999 -0.38752,0.44163 -0.58251,1.02829 -0.58499,1.75998 0.0833,1.29578 0.52664,2.3841 1.32998,3.26496 0.8033,0.88078 1.82662,1.33911 3.06996,1.37498 0.94663,-0.025 1.79329,-0.28503 2.53997,-0.77999 0.74663,-0.49503 1.35329,-1.07502 1.81998,-1.73998 0.57996,-0.75835 1.13995,-1.70167 1.67998,-2.82996 0.53996,-1.12835 1.05995,-2.45166 1.55998,-3.96995 0.31246,-0.95335 0.63745,-2.03667 0.97498,-3.24996 0.33746,-1.21334 0.73246,-2.71665 1.18499,-4.50994 1.21575,-4.8797 2.3219,-9.52902 3.31847,-13.94797 0.99649,-4.41895 1.94858,-8.92902 2.85626,-13.5302 0.9076,-4.60116 1.83598,-9.61491 2.78515,-15.04128 0.46412,-2.82161 0.85578,-5.08824 1.17499,-6.79992 0.31911,-1.71161 0.66077,-3.45825 1.02499,-5.23993 0.58661,-2.96575 1.10326,-5.34406 1.54998,-7.13491 0.44661,-1.79076 0.90327,-3.31907 1.36998,-4.58494 0.42327,-1.1666 0.8566,-2.09325 1.29998,-2.77996 0.44327,-0.68661 0.8766,-1.09327 1.29998,-1.21999 0.0733,-0.0333 0.16661,-0.0466 0.28,-0.04 0.20744,0.002 0.43244,0.0384 0.67499,0.11 0.24244,0.0717 0.45744,0.16839 0.64499,0.29 0.13911,0.0984 0.30078,0.23172 0.485,0.39999 0.1841,0.16839 0.27577,0.26172 0.27499,0.28 -0.61338,0.14505 -1.1167,0.38505 -1.50998,0.71999 -0.39338,0.33505 -0.59671,0.85504 -0.60999,1.55998 0.0166,0.67087 0.23328,1.2092 0.64999,1.61498 0.41661,0.40587 0.93327,0.61421 1.54998,0.62499 0.75994,-0.0108 1.32993,-0.23911 1.70998,-0.68499 0.37994,-0.44578 0.56993,-1.0441 0.56999,-1.79498 -0.0884,-1.2941 -0.53172,-2.37575 -1.32998,-3.24495 -0.79838,-0.86911 -1.9017,-1.32077 -3.30996,-1.35499 -0.48171,0.008 -0.93837,0.10256 -1.36998,0.285 -0.43171,0.18255 -0.82837,0.40755 -1.18998,0.67499 -1.70837,1.37504 -3.09168,3.575 -4.14995,6.59992 -1.05837,3.025 -1.98169,6.18496 -2.76996,9.47987 -1.22917,4.92837 -2.34422,9.6004 -3.34515,14.01612 -1.00101,4.41576 -1.9531,8.92484 -2.85626,13.52723 -0.90323,4.60242 -1.82273,9.64778 -2.75848,15.1361 -0.56836,3.30495 -1.07169,6.13491 -1.50998,8.4899 -0.43836,2.35495 -0.88169,4.56492 -1.32998,6.62991 -0.73336,3.4591 -1.43668,6.16073 -2.10998,8.10489 -0.67335,1.94412 -1.35667,3.25576 -2.04997,3.93495 -0.21003,0.19747 -0.39002,0.33247 -0.53999,0.405 -0.15003,0.0725 -0.33003,0.0975 -0.54,0.075 -0.34002,0.002 -0.66001,-0.0617 -0.95998,-0.19 -0.30003,-0.12836 -0.58002,-0.33169 -0.83999,-0.60999 -0.08,-0.0625 -0.14003,-0.11753 -0.18,-0.165 -0.04,-0.0475 -0.06,-0.0725 -0.06,-0.075 0.6133,-0.14503 1.11663,-0.38502 1.50998,-0.71999 0.3933,-0.33503 0.59663,-0.85502 0.60999,-1.55998 z m 20.31974,0 c -0.0167,-0.67085 -0.23338,-1.20918 -0.64999,-1.61498 -0.41671,-0.40586 -0.93337,-0.61419 -1.54998,-0.62499 -0.74254,0.0116 -1.30753,0.2383 -1.69498,0.67999 -0.38754,0.44163 -0.58253,1.02829 -0.58499,1.75998 0.0833,1.29578 0.52662,2.3841 1.32998,3.26496 0.80328,0.88078 1.8266,1.33911 3.06996,1.37498 0.94661,-0.025 1.79326,-0.28503 2.53997,-0.77999 0.7466,-0.49503 1.35326,-1.07502 1.81998,-1.73998 0.57994,-0.75835 1.13993,-1.70167 1.67998,-2.82996 0.53993,-1.12835 1.05993,-2.45166 1.55998,-3.96995 0.31244,-0.95335 0.63743,-2.03667 0.97498,-3.24996 0.33744,-1.21334 0.73244,-2.71665 1.18499,-4.50994 1.21573,-4.8797 2.32188,-9.52902 3.31847,-13.94797 0.99647,-4.41895 1.94856,-8.92902 2.85626,-13.5302 0.90758,-4.60116 1.83596,-9.61491 2.78515,-15.04128 0.4641,-2.82161 0.85576,-5.08824 1.17499,-6.79992 0.31909,-1.71161 0.66075,-3.45825 1.02498,-5.23993 0.58659,-2.96575 1.10325,-5.34406 1.54998,-7.13491 0.44659,-1.79076 0.90325,-3.31907 1.36999,-4.58494 0.42325,-1.1666 0.85658,-2.09325 1.29998,-2.77996 0.44325,-0.68661 0.87658,-1.09327 1.29998,-1.21999 0.0733,-0.0333 0.16659,-0.0466 0.28,-0.04 0.20742,0.002 0.43242,0.0384 0.67499,0.11 0.24242,0.0717 0.45742,0.16839 0.64499,0.29 0.13909,0.0984 0.30075,0.23172 0.485,0.39999 0.18408,0.16839 0.27575,0.26172 0.27499,0.28 -0.6134,0.14505 -1.11673,0.38505 -1.50998,0.71999 -0.3934,0.33505 -0.59673,0.85504 -0.60999,1.55998 0.0166,0.67087 0.23325,1.2092 0.64999,1.61498 0.41659,0.40587 0.93325,0.61421 1.54998,0.62499 0.75991,-0.0108 1.32991,-0.23911 1.70998,-0.68499 0.37992,-0.44578 0.56991,-1.0441 0.56999,-1.79498 -0.0884,-1.2941 -0.53174,-2.37575 -1.32998,-3.24495 -0.7984,-0.86911 -1.90172,-1.32077 -3.30996,-1.35499 -0.48173,0.008 -0.93839,0.10256 -1.36998,0.285 -0.43174,0.18255 -0.8284,0.40755 -1.18999,0.67499 -1.70838,1.37504 -3.09169,3.575 -4.14994,6.59992 -1.05839,3.025 -1.98171,6.18496 -2.76997,9.47987 -1.22918,4.92837 -2.34423,9.6004 -3.34514,14.01612 -1.00103,4.41576 -1.95312,8.92484 -2.85626,13.52723 -0.90326,4.60242 -1.82275,9.64778 -2.75848,15.1361 -0.56838,3.30495 -1.07171,6.13491 -1.50998,8.4899 -0.43838,2.35495 -0.88171,4.56492 -1.32998,6.62991 -0.73338,3.4591 -1.4367,6.16073 -2.10998,8.10489 -0.67337,1.94412 -1.3567,3.25576 -2.04997,3.93495 -0.21005,0.19747 -0.39005,0.33247 -0.53999,0.405 -0.15005,0.0725 -0.33005,0.0975 -0.54,0.075 -0.34004,0.002 -0.66004,-0.0617 -0.95998,-0.19 -0.30005,-0.12836 -0.58004,-0.33169 -0.83999,-0.60999 -0.08,-0.0625 -0.14005,-0.11753 -0.18,-0.165 -0.04,-0.0475 -0.06,-0.0725 -0.06,-0.075 0.61328,-0.14503 1.1166,-0.38502 1.50998,-0.71999 0.39328,-0.33503 0.59661,-0.85502 0.60999,-1.55998 z" /></g><path d="m 341.5,401.1875 c -9.21407,0 -17.51119,1.41617 -23.625,3.75 -3.05691,1.16691 -5.56849,2.55168 -7.375,4.1875 -1.80651,1.63582 -2.9375,3.59497 -2.9375,5.71875 0,2.12378 1.13099,4.08293 2.9375,5.71875 1.80651,1.63582 4.31809,3.02059 7.375,4.1875 6.11381,2.33383 14.41093,3.71875 23.625,3.71875 l 3.40625,0 c 9.21407,0 17.51119,-1.38492 23.625,-3.71875 3.05691,-1.16691 5.56849,-2.55168 7.375,-4.1875 1.80651,-1.63582 2.9375,-3.59497 2.9375,-5.71875 0,-2.12378 -1.13099,-4.08293 -2.9375,-5.71875 -1.80651,-1.63582 -4.31809,-3.02059 -7.375,-4.1875 -6.11381,-2.33383 -14.41093,-3.75 -23.625,-3.75 l -3.40625,0 z m 0,2.21875 3.40625,0 c 8.98871,0 17.07976,1.39346 22.84375,3.59375 2.882,1.10014 5.17751,2.38268 6.6875,3.75 1.50999,1.36732 2.21875,2.74326 2.21875,4.09375 0,1.35049 -0.70876,2.69518 -2.21875,4.0625 -1.50999,1.36732 -3.8055,2.68111 -6.6875,3.78125 -5.76399,2.20029 -13.85504,3.59375 -22.84375,3.59375 l -3.40625,0 c -8.98871,0 -17.07976,-1.39346 -22.84375,-3.59375 -2.882,-1.10014 -5.17751,-2.41393 -6.6875,-3.78125 -1.50999,-1.36732 -2.21875,-2.71201 -2.21875,-4.0625 0,-1.35049 0.70876,-2.72643 2.21875,-4.09375 1.50999,-1.36732 3.8055,-2.64986 6.6875,-3.75 5.76399,-2.20029 13.85504,-3.59375 22.84375,-3.59375 z" /></g></svg></div>';
        return new eqEd.EquationDom(this, htmlRep);
    };
})();

/* End eq/js/equation-components/misc/contourTripleIntegralSymbol.js*/

/* Begin eq/js/equation-components/containers/integralUpperLimitContainer.js*/

eqEd.IntegralUpperLimitContainer = function(parent) {
    eqEd.Container.call(this, parent);
    this.className = "eqEd.IntegralUpperLimitContainer";
    this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);
    
    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftVal = 0;
            if (this.parent.isInline) {
                leftVal = this.parent.symbol.width + this.parent.inlineLimitGap * fontHeight;
            } else {
                var maxWidthList = [];
                if (this.parent.hasUpperLimit) {
                    maxWidthList.push(this.parent.upperLimitContainer.width);
                }
                if (this.parent.hasLowerLimit) {
                    maxWidthList.push(this.parent.lowerLimitContainer.width);
                }
                maxWidthList.push(this.parent.symbol.width);
                var maxWidth = maxWidthList.max();
                leftVal = 0.5 * (maxWidth - this.width);
            }
            
            return leftVal;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var topVal = 0;
            if (this.parent.isInline) {
                var leftPartTopAlign = 0;
                if (this.height > this.parent.symbol.height * this.parent.inlineUpperLimitOverlap) {
                    leftPartTopAlign = (0.5 - this.parent.inlineUpperLimitOverlap) * this.parent.symbol.height + this.height;
                } else {
                    leftPartTopAlign = 0.5 * this.parent.symbol.height;
                }
                topVal = (this.parent.topAlign - this.parent.padTop * fontHeight) - leftPartTopAlign;
            } else {
                var leftPartTopAlign = this.height + 0.5 * this.parent.symbol.height + this.parent.upperLimitGap * fontHeight;
                topVal = (this.parent.topAlign - this.parent.padTop * fontHeight) - leftPartTopAlign;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var fontSizeVal = "";
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            if (actualParentContainer.fontSize === "fontSizeSmaller" || actualParentContainer.fontSize === "fontSizeSmallest") {
                fontSizeVal = "fontSizeSmallest";
            } else {
                fontSizeVal = "fontSizeSmaller";
            }
            return fontSizeVal;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.IntegralUpperLimitContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.IntegralUpperLimitContainer.prototype.constructor = eqEd.IntegralUpperLimitContainer;
    eqEd.IntegralUpperLimitContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer integralUpperLimitContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/integralUpperLimitContainer.js*/

/* Begin eq/js/equation-components/containers/integralLowerLimitContainer.js*/

eqEd.IntegralLowerLimitContainer = function(parent) {
    eqEd.Container.call(this, parent);
    this.className = "eqEd.IntegralLowerLimitContainer";
    this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);
    
    this.inlineLeftOverlap = 0.55;

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftVal = 0;
            if (this.parent.isInline) {
                leftVal = this.parent.symbol.width + (this.parent.inlineLimitGap - this.inlineLeftOverlap) * fontHeight;
            } else {
                var maxWidthList = [];
                if (this.parent.hasUpperLimit) {
                    maxWidthList.push(this.parent.upperLimitContainer.width);
                }
                if (this.parent.hasLowerLimit) {
                    maxWidthList.push(this.parent.lowerLimitContainer.width);
                }
                maxWidthList.push(this.parent.symbol.width);
                var maxWidth = maxWidthList.max();
                leftVal = 0.5 * (maxWidth - this.width);
            }
            return leftVal;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var topVal = 0;
            if (this.parent.isInline) {
                var additionalTopAlign = 0;
                if (this.height > this.parent.symbol.height * this.parent.inlineLowerLimitOverlap) {
                    additionalTopAlign = (0.5 - this.parent.inlineLowerLimitOverlap) * this.parent.symbol.height;
                } else {
                    additionalTopAlign = 0.5 * this.parent.symbol.height - this.height;
                }
                topVal = (this.parent.topAlign - this.parent.padTop * fontHeight) + additionalTopAlign;
            } else {
                topVal = (this.parent.topAlign - this.parent.padTop * fontHeight) + this.parent.symbol.height * 0.5 + this.parent.lowerLimitGap * fontHeight;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var fontSizeVal = "";
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            if (actualParentContainer.fontSize === "fontSizeSmaller" || actualParentContainer.fontSize === "fontSizeSmallest") {
                fontSizeVal = "fontSizeSmallest";
            } else {
                fontSizeVal = "fontSizeSmaller";
            }
            return fontSizeVal;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.IntegralLowerLimitContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.IntegralLowerLimitContainer.prototype.constructor = eqEd.IntegralLowerLimitContainer;
    eqEd.IntegralLowerLimitContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer integralLowerLimitContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/integralLowerLimitContainer.js*/

/* Begin eq/js/equation-components/misc/word.js*/

eqEd.Word = function(parent, characters, fontStyle) {
    eqEd.BoundEquationComponent.call(this, parent); // call super constructor.
    this.className = "eqEd.Word";
    
    this.characters = characters.split("");
    this.fontStyle = fontStyle;
    this.domObj = this.buildDomObj();
    if (IEVersion >= 9) {
        if (this.fontStyle === "MathJax_MathItalic") {
            this.adjustTop = 0.345;
        } else {
            this.adjustTop = 0.3;
        }
    } else {
        if (this.fontStyle === "MathJax_MathItalic") {
            this.adjustTop = 0.025;
        }
    }
    
    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
        	var widthVal = 0;
        	for (var i = 0; i < this.characters.length; i++) {
        		widthVal += this.equation.fontMetrics.width[this.characters[i]][this.fontStyle][this.parent.parent.fontSize];
        	}
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the height calculation
    var height = 0;
    this.properties.push(new Property(this, "height", height, {
        get: function() {
            return height;
        },
        set: function(value) {
            height = value;
        },
        compute: function() {
            var fontHeight = this.getFontHeight();
            return 1  * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateHeight(this.height);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.Word.prototype = Object.create(eqEd.BoundEquationComponent.prototype);
    eqEd.Word.prototype.constructor = eqEd.Word;
    eqEd.Word.prototype.buildDomObj = function() {
        return new eqEd.EquationDom(this,
            '<div class="symbol ' + this.fontStyle + '">' + this.characters.join("") + '</div>');
    };
})();

/* End eq/js/equation-components/misc/word.js*/

/* Begin eq/js/equation-components/misc/functionWord.js*/

eqEd.FunctionWord = function(parent, characters, fontStyle) {
    eqEd.Word.call(this, parent, characters, fontStyle); // call super constructor.
    this.className = "eqEd.FunctionWord";

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.FunctionWord.prototype = Object.create(eqEd.Word.prototype);
    eqEd.FunctionWord.prototype.constructor = eqEd.FunctionWord;
})();

/* End eq/js/equation-components/misc/functionWord.js*/

/* Begin eq/js/equation-components/wrappers/functionWrapper.js*/

eqEd.FunctionWrapper = function(equation, functionCharacters, fontStyle) {
	eqEd.Wrapper.call(this, equation); // call super constructor.
	this.className = "eqEd.FunctionWrapper";

    this.word = new eqEd.FunctionWord(this, functionCharacters, fontStyle);
	this.domObj = this.buildDomObj();
	this.domObj.append(this.word.domObj);
	this.childNoncontainers = [this.word];

    this.padLeft = 0.1;

    // Set up the padRight calculation
    var padRight = 0;
    this.properties.push(new Property(this, "padRight", padRight, {
        get: function() {
            return padRight;
        },
        set: function(value) {
            padRight = value;
        },
        compute: function() {
            var padRightVal = 0.175;
            if (this.index !== this.parent.wrappers.length - 1) { 
                if (this.parent.wrappers[this.index + 1] instanceof eqEd.SuperscriptWrapper
                    || this.parent.wrappers[this.index + 1] instanceof eqEd.SubscriptWrapper) {
                    padRightVal = 0;
                } else if (this.parent.wrappers[this.index + 1] instanceof eqEd.BracketWrapper
                    || this.parent.wrappers[this.index + 1] instanceof eqEd.BracketPairWrapper) {
                    padRightVal = 0.05;
                }
            }
            return padRightVal;
        },
        updateDom: function() {}
    }));

	// Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            return this.word.width;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
            return 0.5 * this.word.height;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
            return 0.5 * this.word.height;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.FunctionWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.FunctionWrapper.prototype.constructor = eqEd.FunctionWrapper;
    eqEd.FunctionWrapper.prototype.clone = function() {
    	return new this.constructor(this.equation, this.word.characters.join(""), this.word.fontStyle);
    };
    eqEd.FunctionWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper symbolWrapper"></div>')
    };
    eqEd.FunctionWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: this.word.characters.join(""),
            operands: null
        };
        return jsonObj;
    };
    eqEd.FunctionWrapper.constructFromJsonObj = function(jsonObj, equation) {
      var functionWrapper = new eqEd.FunctionWrapper(equation, jsonObj.value, "MathJax_Main");
      return functionWrapper;
    }
})();

/* End eq/js/equation-components/wrappers/functionWrapper.js*/

/* Begin eq/js/equation-components/wrappers/functionLowerWrapper.js*/

eqEd.FunctionLowerWrapper = function(equation, characters, fontStyle) {
    eqEd.FunctionWrapper.call(this, equation, characters, fontStyle); // call super constructor.
    this.className = "eqEd.FunctionLowerWrapper";

    // topAlign, bottomAlign, width has already been added to 
    // properties in superclass needs removed to be overriden
    for(var i = 0; i < this.properties.length; i++) {
        if (this.properties[i].propName === "topAlign"
            || this.properties[i].propName === "bottomAlign"
            || this.properties[i].propName === "width") {
            this.properties.splice(i, 1);
        }
    }

    this.belowFunctionGap = -0.075;

    this.functionWord = new eqEd.FunctionLowerWord(this, characters, fontStyle);
    this.functionLowerContainer = new eqEd.FunctionLowerContainer(this);
    this.domObj = this.buildDomObj();
    this.domObj.append(this.functionWord.domObj);
    this.domObj.append(this.functionLowerContainer.domObj);
    
    this.childNoncontainers = [this.functionWord];
    this.childContainers = [this.functionLowerContainer];

    this.padLeft = 0;
    this.padRight = 0.05;

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            var widthVal = 0;
            var topWidth = this.functionWord.width;
            widthVal = (topWidth > this.functionLowerContainer.width) ? topWidth : this.functionLowerContainer.width;
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
            return 0.5 * this.functionWord.height;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            return 0.5 * this.functionWord.height + this.belowFunctionGap * fontHeight + this.functionLowerContainer.height;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.FunctionLowerWrapper.prototype = Object.create(eqEd.FunctionWrapper.prototype);
    eqEd.FunctionLowerWrapper.prototype.constructor = eqEd.FunctionLowerWrapper;
    eqEd.FunctionLowerWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper functionLowerWrapper"></div>')
    }
    eqEd.FunctionLowerWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation, this.word.characters.join(""), this.word.fontStyle);
        copy.functionLowerContainer = this.functionLowerContainer.clone();
        copy.functionLowerContainer.parent = copy;
        copy.domObj = copy.buildDomObj();
        copy.domObj.append(copy.word.domObj);
        copy.domObj.append(copy.functionLowerContainer.domObj);
        
        copy.childNoncontainers = [copy.word];
        copy.childContainers = [copy.functionLowerContainer];

        return copy;
    }
    eqEd.FunctionLowerWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: this.functionWord.characters.join(""),
            operands: {
                lower: this.functionLowerContainer.buildJsonObj()
            }
        };
        return jsonObj;
    };
    eqEd.FunctionLowerWrapper.constructFromJsonObj = function(jsonObj, equation) {
      var functionLowerWrapper = new eqEd.FunctionLowerWrapper(equation, jsonObj.value, "MathJax_Main");
      for (var i = 0; i < jsonObj.operands.lower.length; i++) {
        var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.lower[i].type);
        var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.lower[i], equation);
        functionLowerWrapper.functionLowerContainer.addWrappers([i, innerWrapper]);
      }
      return functionLowerWrapper;
    };
})();

/* End eq/js/equation-components/wrappers/functionLowerWrapper.js*/

/* Begin eq/js/equation-components/misc/functionLowerWord.js*/

eqEd.FunctionLowerWord = function(parent, characters, fontStyle) {
    eqEd.Word.call(this, parent, characters, fontStyle); // call super constructor.
    this.className = "eqEd.FunctionLowerWord";

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftOffset = 0.5 * ((this.parent.width - (this.parent.padLeft + this.parent.padRight) * fontHeight) - this.width);
            return leftOffset;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.FunctionLowerWord.prototype = Object.create(eqEd.Word.prototype);
    eqEd.FunctionLowerWord.prototype.constructor = eqEd.FunctionLowerWord;
})();

/* End eq/js/equation-components/misc/functionLowerWord.js*/

/* Begin eq/js/equation-components/containers/functionLowerContainer.js*/

eqEd.FunctionLowerContainer = function(parent) {
    eqEd.Container.call(this, parent);
    this.className = "eqEd.FunctionLowerContainer";
    this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftOffset = 0.5 * ((this.parent.width - (this.parent.padLeft + this.parent.padRight) * fontHeight) - this.width);
            return leftOffset;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            return this.parent.functionWord.height + this.parent.belowFunctionGap * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var fontSizeVal = "";
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            if (actualParentContainer.fontSize === "fontSizeSmaller" || actualParentContainer.fontSize === "fontSizeSmallest") {
                fontSizeVal = "fontSizeSmallest";
            } else {
                fontSizeVal = "fontSizeSmaller";
            }
            return fontSizeVal;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.FunctionLowerContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.FunctionLowerContainer.prototype.constructor = eqEd.FunctionLowerContainer;
    eqEd.FunctionLowerContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer functionLowerContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/functionLowerContainer.js*/

/* Begin eq/js/equation-components/wrappers/logLowerWrapper.js*/

eqEd.LogLowerWrapper = function(equation) {
    eqEd.FunctionWrapper.call(this, equation,'log', 'MathJax_Main'); // call super constructor.
    this.className = "eqEd.LogLowerWrapper";

    // topAlign, bottomAlign, width has already been added to 
    // properties in superclass needs removed to be overriden
    for(var i = 0; i < this.properties.length; i++) {
        if (this.properties[i].propName === "topAlign"
            || this.properties[i].propName === "bottomAlign"
            || this.properties[i].propName === "width") {
            this.properties.splice(i, 1);
        }
    }

    this.logLowerOverlap = 0.75;

    this.functionWord = new eqEd.LogLowerWord(this);
    this.functionLowerContainer = new eqEd.LogLowerContainer(this);
    this.domObj = this.buildDomObj();
    this.domObj.append(this.functionWord.domObj);
    this.domObj.append(this.functionLowerContainer.domObj);
    
    this.childNoncontainers = [this.functionWord];
    this.childContainers = [this.functionLowerContainer];

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            return this.functionWord.width + this.functionLowerContainer.width;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
            return 0.5 * this.functionWord.height;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
            var fontHeightNested = this.equation.fontMetrics.height[this.functionLowerContainer.fontSize];
            return this.functionWord.height - this.logLowerOverlap * fontHeightNested + this.functionLowerContainer.height - this.topAlign;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LogLowerWrapper.prototype = Object.create(eqEd.FunctionWrapper.prototype);
    eqEd.LogLowerWrapper.prototype.constructor = eqEd.LogLowerWrapper;
    eqEd.LogLowerWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper logLowerWrapper"></div>')
    };
    eqEd.LogLowerWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation);
        copy.functionLowerContainer = this.functionLowerContainer.clone();
        copy.functionLowerContainer.parent = copy;
        copy.domObj = copy.buildDomObj();
        copy.domObj.append(copy.functionWord.domObj);
        copy.domObj.append(copy.functionLowerContainer.domObj);
        
        copy.childNoncontainers = [copy.functionWord];
        copy.childContainers = [copy.functionLowerContainer];

        return copy;
    };
    eqEd.LogLowerWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: null,
            operands: {
                lower: this.functionLowerContainer.buildJsonObj()
            }
        };
        return jsonObj;
    };
    eqEd.LogLowerWrapper.constructFromJsonObj = function(jsonObj, equation) {
        var logLowerWrapper = new eqEd.LogLowerWrapper(equation);
        for (var i = 0; i < jsonObj.operands.lower.length; i++) {
            var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.lower[i].type);
            var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.lower[i], equation);
            logLowerWrapper.functionLowerContainer.addWrappers([i, innerWrapper]);
        }
        return logLowerWrapper;
    };
})();

/* End eq/js/equation-components/wrappers/logLowerWrapper.js*/

/* Begin eq/js/equation-components/misc/logLowerWord.js*/

eqEd.LogLowerWord = function(parent) {
    eqEd.Word.call(this, parent, 'log', "MathJax_Main"); // call super constructor.
    this.className = "eqEd.LogLowerWord";

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.LogLowerWord.prototype = Object.create(eqEd.Word.prototype);
    eqEd.LogLowerWord.prototype.constructor = eqEd.LogLowerWord;
})();

/* End eq/js/equation-components/misc/logLowerWord.js*/

/* Begin eq/js/equation-components/containers/logLowerContainer.js*/

eqEd.LogLowerContainer = function(parent) {
    eqEd.Container.call(this, parent);
    this.className = "eqEd.LogLowerContainer";
    this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            return this.parent.functionWord.width;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.fontSize];
            return this.parent.functionWord.height - this.parent.logLowerOverlap * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var fontSizeVal = "";
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            if (actualParentContainer.fontSize === "fontSizeSmaller" || actualParentContainer.fontSize === "fontSizeSmallest") {
                fontSizeVal = "fontSizeSmallest";
            } else {
                fontSizeVal = "fontSizeSmaller";
            }
            return fontSizeVal;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LogLowerContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.LogLowerContainer.prototype.constructor = eqEd.LogLowerContainer;
    eqEd.LogLowerContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer logLowerContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/logLowerContainer.js*/

/* Begin eq/js/equation-components/wrappers/limitWrapper.js*/

eqEd.LimitWrapper = function(equation) {
    eqEd.FunctionWrapper.call(this, equation, 'lim', 'MathJax_Main'); // call super constructor.
    this.className = "eqEd.LimitWrapper";

    // topAlign, bottomAlign, width has already been added to 
    // properties in superclass needs removed to be overriden
    for(var i = 0; i < this.properties.length; i++) {
        if (this.properties[i].propName === "topAlign"
            || this.properties[i].propName === "bottomAlign"
            || this.properties[i].propName === "width") {
            this.properties.splice(i, 1);
        }
    }

    this.leftLimitContainerGap = 0;
    this.rightLimitContainerGap = 0;
    this.belowLimitGap = -0.18;

    this.limitWord = new eqEd.LimitWord(this);
    this.limitLeftContainer = new eqEd.LimitLeftContainer(this);
    this.limitRightContainer = new eqEd.LimitRightContainer(this);
    this.symbol = new eqEd.LimitSymbol(this);
    this.domObj = this.buildDomObj();
    this.domObj.append(this.limitWord.domObj);
    this.domObj.append(this.limitLeftContainer.domObj);
    this.domObj.append(this.limitRightContainer.domObj);
    this.domObj.append(this.symbol.domObj);
    
    this.childNoncontainers = [this.symbol, this.limitWord];
    this.childContainers = [this.limitLeftContainer, this.limitRightContainer];

    // Set up the bottomHalfWidth calculation
    var bottomHalfWidth = 0;
    this.properties.push(new Property(this, "bottomHalfWidth", bottomHalfWidth, {
        get: function() {
            return bottomHalfWidth;
        },
        set: function(value) {
            bottomHalfWidth = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            var bottomHalfWidthVal = this.limitLeftContainer.width + this.leftLimitContainerGap * fontHeight + this.symbol.width + this.rightLimitContainerGap * fontHeight + this.limitRightContainer.width;
            return bottomHalfWidthVal;
        },
        updateDom: function() {}
    }));

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            var widthVal = 0;
            var topWidth = this.limitWord.width;
            widthVal = (topWidth > this.bottomHalfWidth) ? topWidth : this.bottomHalfWidth;
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
            return 0.5 * this.limitWord.height;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            var bottomAlignVal = 0;
            var maxBottomHalfHeight = [this.symbol.height, this.limitLeftContainer.height, this.limitRightContainer.height].max();
            bottomAlignVal = 0.5 * this.limitWord.height + this.belowLimitGap * fontHeight + maxBottomHalfHeight;
            return bottomAlignVal;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LimitWrapper.prototype = Object.create(eqEd.FunctionWrapper.prototype);
    eqEd.LimitWrapper.prototype.constructor = eqEd.LimitWrapper;
    eqEd.LimitWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper limitWrapper"></div>')
    };
    eqEd.LimitWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation);
        copy.limitWord = new eqEd.LimitWord(copy);
        copy.limitLeftContainer = this.limitLeftContainer.clone();
        copy.limitLeftContainer.parent = copy;
        copy.limitRightContainer = this.limitRightContainer.clone();
        copy.limitRightContainer.parent = copy;
        copy.symbol = new eqEd.LimitSymbol(copy);
        copy.domObj = copy.buildDomObj();
        copy.domObj.append(copy.limitWord.domObj);
        copy.domObj.append(copy.limitLeftContainer.domObj);
        copy.domObj.append(copy.limitRightContainer.domObj);
        copy.domObj.append(copy.symbol.domObj);
        
        copy.childNoncontainers = [copy.symbol, copy.limitWord];
        copy.childContainers = [copy.limitLeftContainer, copy.limitRightContainer];

        return copy;
    };
    eqEd.LimitWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: null,
            operands: {
                left: this.limitLeftContainer.buildJsonObj(),
                right: this.limitRightContainer.buildJsonObj()
            }
        };
        return jsonObj;
    };
    eqEd.LimitWrapper.constructFromJsonObj = function(jsonObj, equation) {
        var limitWrapper = new eqEd.LimitWrapper(equation);
        for (var i = 0; i < jsonObj.operands.left.length; i++) {
            var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.left[i].type);
            var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.left[i], equation);
            limitWrapper.limitLeftContainer.addWrappers([i, innerWrapper]);
        }
        for (var i = 0; i < jsonObj.operands.right.length; i++) {
            var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.right[i].type);
            var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.right[i], equation);
            limitWrapper.limitRightContainer.addWrappers([i, innerWrapper]);
        }
        return limitWrapper;
    };
})();

/* End eq/js/equation-components/wrappers/limitWrapper.js*/

/* Begin eq/js/equation-components/containers/limitLeftContainer.js*/

eqEd.LimitLeftContainer = function(parent) {
    eqEd.Container.call(this, parent);
    this.className = "eqEd.LimitLeftContainer";
    this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftOffset = 0.5 * ((this.parent.width - (this.parent.padLeft + this.parent.padRight) * fontHeight) - this.parent.bottomHalfWidth);
            return leftOffset;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var bottomHalfMaxTopAlign = 0;
            var topOffset = 0;
            if (this.wrappers.length > 0 && this.parent.limitRightContainer.wrappers.length > 0) {
                bottomHalfMaxTopAlign = [this.wrappers[this.maxTopAlignIndex].topAlign, 0.5 * this.parent.symbol.height, this.parent.limitRightContainer.wrappers[this.parent.limitRightContainer.maxTopAlignIndex].topAlign].max();
                topOffset = bottomHalfMaxTopAlign - this.wrappers[this.maxTopAlignIndex].topAlign;
            }
            return this.parent.limitWord.height + this.parent.belowLimitGap * fontHeight + topOffset;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var fontSizeVal = "";
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            if (actualParentContainer.fontSize === "fontSizeSmaller" || actualParentContainer.fontSize === "fontSizeSmallest") {
                fontSizeVal = "fontSizeSmallest";
            } else {
                fontSizeVal = "fontSizeSmaller";
            }
            return fontSizeVal;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LimitLeftContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.LimitLeftContainer.prototype.constructor = eqEd.LimitLeftContainer;
    eqEd.LimitLeftContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer limitLeftContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/limitLeftContainer.js*/

/* Begin eq/js/equation-components/containers/limitRightContainer.js*/

eqEd.LimitRightContainer = function(parent) {
    eqEd.Container.call(this, parent);
    this.className = "eqEd.LimitRightContainer";
    this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftOffset = 0.5 * ((this.parent.width - (this.parent.padLeft + this.parent.padRight) * fontHeight) - this.parent.bottomHalfWidth);
            return leftOffset + this.parent.limitLeftContainer.width + this.parent.leftLimitContainerGap * fontHeight + this.parent.symbol.width + this.parent.rightLimitContainerGap * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var bottomHalfMaxTopAlign = 0;
            var topOffset = 0;
            if (this.wrappers.length > 0 && this.parent.limitLeftContainer.wrappers.length > 0) {
                bottomHalfMaxTopAlign = [this.wrappers[this.maxTopAlignIndex].topAlign, 0.5 * this.parent.symbol.height, this.parent.limitLeftContainer.wrappers[this.parent.limitLeftContainer.maxTopAlignIndex].topAlign].max();
                topOffset = bottomHalfMaxTopAlign - this.wrappers[this.maxTopAlignIndex].topAlign;
            }
            return this.parent.limitWord.height + this.parent.belowLimitGap * fontHeight + topOffset;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var fontSizeVal = "";
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            if (actualParentContainer.fontSize === "fontSizeSmaller" || actualParentContainer.fontSize === "fontSizeSmallest") {
                fontSizeVal = "fontSizeSmallest";
            } else {
                fontSizeVal = "fontSizeSmaller";
            }
            return fontSizeVal;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.LimitRightContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.LimitRightContainer.prototype.constructor = eqEd.LimitRightContainer;
    eqEd.LimitRightContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer limitRightContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/limitRightContainer.js*/

/* Begin eq/js/equation-components/misc/limitWord.js*/

eqEd.LimitWord = function(parent) {
    eqEd.Word.call(this, parent, "lim", "MathJax_Main"); // call super constructor.
    this.className = "eqEd.LimitWord";

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftOffset = 0.5 * ((this.parent.width - (this.parent.padLeft + this.parent.padRight) * fontHeight) - this.width);
            return leftOffset;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            // remember compute hooks get called.
            return 0;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.LimitWord.prototype = Object.create(eqEd.Word.prototype);
    eqEd.LimitWord.prototype.constructor = eqEd.LimitWord;
})();

/* End eq/js/equation-components/misc/limitWord.js*/

/* Begin eq/js/equation-components/misc/limitSymbol.js*/

eqEd.LimitSymbol = function(parent) {
    eqEd.Symbol.call(this, parent, '→', 'MathJax_Main'); // call super constructor.
    this.className = "eqEd.LimitSymbol";

    this.adjustTop = -0.07;

    // Height has already been added to properties in superclass
    // needs removed to be overriden
    for(var i = 0; i < this.properties.length; i++) {
        if (this.properties[i].propName === "left"
            || this.properties[i].propName === "top") {
            this.properties.splice(i, 1);
        }
    }

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftOffset = 0.5 * ((this.parent.width - (this.parent.padLeft + this.parent.padRight) * fontHeight) - this.parent.bottomHalfWidth);
            return leftOffset + this.parent.limitLeftContainer.width + this.parent.leftLimitContainerGap * fontHeight;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var bottomHalfMaxTopAlign = 0;
            var topOffset = 0;
            if (this.parent.limitLeftContainer.wrappers.length > 0
                && this.parent.limitRightContainer.wrappers.length > 0) {
                bottomHalfMaxTopAlign = [this.parent.limitLeftContainer.wrappers[this.parent.limitLeftContainer.maxTopAlignIndex].topAlign, 0.5 * this.height, this.parent.limitRightContainer.wrappers[this.parent.limitRightContainer.maxTopAlignIndex].topAlign].max();
                topOffset = bottomHalfMaxTopAlign - 0.5 * this.height;
            }
            return this.parent.limitWord.height + this.parent.belowLimitGap * fontHeight + topOffset;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

};
(function() {
    // subclass extends superclass
    eqEd.LimitSymbol.prototype = Object.create(eqEd.Symbol.prototype);
    eqEd.LimitSymbol.prototype.constructor = eqEd.LimitSymbol;
})();

/* End eq/js/equation-components/misc/limitSymbol.js*/

/* Begin eq/js/equation-components/wrappers/matrixWrapper.js*/

eqEd.MatrixWrapper = function(equation, numRows, numCols, horAlign) {
    eqEd.Wrapper.call(this, equation); // call super constructor.
    this.className = "eqEd.MatrixWrapper";

    this.numRows = numRows;
    this.numCols = numCols;
    this.horAlign = horAlign;
    this.horGap = 1;
    this.vertGap = 0.25;

    if (this.numRows === 2 && this.numCols === 1) {
        this.padLeft = 0;
        this.padRight = 0;
    } else {
        this.padLeft = 0.25;
        this.padRight = 0.25;
    }

    this.domObj = this.buildDomObj();

    this.childContainers = [];
    this.matrixContainers = [];
    for (var i = 0; i < this.numRows; i++) {
        var row = [];
        for (var j = 0; j < this.numCols; j++) {
            var matrixContainer = new eqEd.MatrixContainer(this, i, j);
            this.domObj.append(matrixContainer.domObj);
            row.push(matrixContainer);
            this.childContainers.push(matrixContainer);
        }
        this.matrixContainers.push(row);
    }

    // Set up the rowTopAligns calculation
    var rowTopAligns = [];
    this.properties.push(new Property(this, "rowTopAligns", rowTopAligns, {
        get: function() {
            return rowTopAligns;
        },
        set: function(value) {
            rowTopAligns = value;
        },
        compute: function() {
            var rowTopAlignsVal = [];
            for (var i = 0; i < this.numRows; i++) {
                var rowTopAlignsList = [];
                for (var j = 0; j < this.numCols; j++) {
                    var topAlign = 0;
                    if (this.matrixContainers[i][j].wrappers.length > 0) {
                        topAlign = this.matrixContainers[i][j].wrappers[this.matrixContainers[i][j].maxTopAlignIndex].topAlign;
                    }
                    rowTopAlignsList.push(topAlign);
                }
                rowTopAlignsVal.push(rowTopAlignsList.max());
            }
            return rowTopAlignsVal;
        },
        updateDom: function() {}
    }));

    // Set up the rowBottomAligns calculation
    var rowBottomAligns = [];
    this.properties.push(new Property(this, "rowBottomAligns", rowBottomAligns, {
        get: function() {
            return rowBottomAligns;
        },
        set: function(value) {
            rowBottomAligns = value;
        },
        compute: function() {
            var rowBottomAlignsVal = [];
            for (var i = 0; i < this.numRows; i++) {
                var rowBottomAlignsList = [];
                for (var j = 0; j < this.numCols; j++) {
                    var bottomAlign = 0;
                    if (this.matrixContainers[i][j].wrappers.length > 0) {
                        bottomAlign = this.matrixContainers[i][j].wrappers[this.matrixContainers[i][j].maxBottomAlignIndex].bottomAlign;
                    }
                    rowBottomAlignsList.push(bottomAlign);
                }
                rowBottomAlignsVal.push(rowBottomAlignsList.max());
            }
            return rowBottomAlignsVal;
        },
        updateDom: function() {}
    }));

    // Set up the colWidths calculation
    var colWidths = [];
    this.properties.push(new Property(this, "colWidths", colWidths, {
        get: function() {
            return colWidths;
        },
        set: function(value) {
            colWidths = value;
        },
        compute: function() {
            var colWidthsVal = [];
            for (var i = 0; i < this.numCols; i++) {
                var colWidthsList = [];
                for (var j = 0; j < this.numRows; j++) {
                    colWidthsList.push(this.matrixContainers[j][i].width);
                }
                colWidthsVal.push(colWidthsList.max());
            }
            return colWidthsVal;
        },
        updateDom: function() {}
    }));

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            var widthVal = 0;
            for (var i = 0; i < this.numCols; i++) {
                widthVal += this.colWidths[i];
            }
            widthVal += (this.numCols - 1) * this.horGap * fontHeight;
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the matrixHeight calculation
    var matrixHeight = 0;
    this.properties.push(new Property(this, "matrixHeight", matrixHeight, {
        get: function() {
            return matrixHeight;
        },
        set: function(value) {
            matrixHeight = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            var matrixHeightVal = 0;
            for (var i = 0; i < this.numRows; i++) {
                matrixHeightVal += this.rowTopAligns[i] + this.rowBottomAligns[i];
            }
            matrixHeightVal += (this.numRows - 1) * this.vertGap * fontHeight;
            return matrixHeightVal;
        },
        updateDom: function() {}
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
            return 0.5 * this.matrixHeight;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
            return 0.5 * this.matrixHeight;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.MatrixWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.MatrixWrapper.prototype.constructor = eqEd.MatrixWrapper;
    eqEd.MatrixWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper matrixWrapper"></div>')
    };
    eqEd.MatrixWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation, this.numRows, this.numCols, this.horAlign);
        copy.domObj = copy.buildDomObj();

        copy.childContainers = [];
        copy.matrixContainers = [];
        for (var i = 0; i < copy.numRows; i++) {
            var row = [];
            for (var j = 0; j < copy.numCols; j++) {
                var matrixContainer = this.matrixContainers[i][j].clone();
                matrixContainer.parent = copy;
                copy.domObj.append(matrixContainer.domObj);
                row.push(matrixContainer);
                copy.childContainers.push(matrixContainer);
            }
            copy.matrixContainers.push(row);
        }

        return copy;
    };
    eqEd.MatrixWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: null
        };
        var jsonMatrixContainers = [];
        for (var i = 0; i < this.matrixContainers.length; i++) {
            var jsonRow = [];
            for (var j = 0; j < this.matrixContainers[i].length; j++) {
                jsonRow.push(this.matrixContainers[i][j].buildJsonObj());
            }
            jsonMatrixContainers.push(jsonRow);
        }
        jsonObj.operands = {
            elements: jsonMatrixContainers
        }
        return jsonObj;
    };
    eqEd.MatrixWrapper.constructFromJsonObj = function(jsonObj, equation) {
        var numRows = jsonObj.operands.elements.length;
        var numCols = jsonObj.operands.elements[0].length;
        var matrixWrapper = new eqEd.MatrixWrapper(equation, numRows, numCols, 'center');
        for (var i = 0; i < jsonObj.operands.elements.length; i++) {
            var matrixRow = jsonObj.operands.elements[i];
            for (var j = 0; j < matrixRow.length; j++) {
                var matrixEntry = matrixRow[j];
                for (var k = 0; k < matrixEntry.length; k++) {
                    var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(matrixEntry[k].type);
                    var innerWrapper = innerWrapperCtor.constructFromJsonObj(matrixEntry[k], equation);
                    matrixWrapper.matrixContainers[i][j].addWrappers([k, innerWrapper]);
                }
            }
        }
        return matrixWrapper;
    };
})();

/* End eq/js/equation-components/wrappers/matrixWrapper.js*/

/* Begin eq/js/equation-components/containers/matrixContainer.js*/

eqEd.MatrixContainer = function(parent, row, col) {
    eqEd.Container.call(this, parent);
    this.className = "eqEd.MatrixContainer";
    this.row = row;
    this.col = col;

    this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);
    
    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftVal = 0;
            for (var i = 0; i < this.col; i++) {
                leftVal += this.parent.colWidths[i];
            }
            if (this.parent.horAlign === 'left') {
                leftVal += 0;
            } else if (this.parent.horAlign === 'center') {
                leftVal += 0.5 * (this.parent.colWidths[this.col] - this.width);
            } else if (this.parent.horAlign === 'right') {
                leftVal += this.parent.colWidths[this.col] - this.width;
            }
            leftVal += this.col * this.parent.horGap * fontHeight;
            return leftVal;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var topVal = 0;
            for (var i = 0; i < this.row; i++) {
                topVal += this.parent.rowTopAligns[i] + this.parent.rowBottomAligns[i];
            }
            if (this.wrappers.length > 0) {
                topVal += this.parent.rowTopAligns[this.row] - this.wrappers[this.maxTopAlignIndex].topAlign;
            }
            topVal += this.row * this.parent.vertGap * fontHeight;
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var fontSizeVal = "";
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            if (actualParentContainer.fontSize === "fontSizeSmaller" || actualParentContainer.fontSize === "fontSizeSmallest") {
                fontSizeVal = "fontSizeSmallest";
            } else {
                fontSizeVal = "fontSizeNormal";
            }
            return fontSizeVal;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.MatrixContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.MatrixContainer.prototype.constructor = eqEd.MatrixContainer;
    eqEd.MatrixContainer.prototype.clone = function() {
      var copy = new this.constructor(this.parent, this.row, this.col);
      var indexAndWrapperList = [];
      for (var i = 0; i < this.wrappers.length; i++) {
        indexAndWrapperList.push([i, this.wrappers[i].clone()]);
      }
      eqEd.Container.prototype.addWrappers.apply(copy, indexAndWrapperList);
      return copy;
    }
    eqEd.MatrixContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer matrixContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/matrixContainer.js*/

/* Begin eq/js/equation-components/wrappers/accentWrapper.js*/

eqEd.AccentWrapper = function(equation, character, fontStyle) {
    eqEd.Wrapper.call(this, equation); // call super constructor.
    this.className = "eqEd.AccentWrapper";

    this.character = character;
    this.fontStyle = fontStyle;

    this.accentSymbol = new eqEd.AccentSymbol(this, character, fontStyle);
    this.accentContainer = new eqEd.AccentContainer(this);
    this.domObj = this.buildDomObj();
    this.domObj.append(this.accentSymbol.domObj);
    this.domObj.append(this.accentContainer.domObj);
    
    this.childNoncontainers = [this.accentSymbol];
    this.childContainers = [this.accentContainer];

    // Set up the width calculation
    var accentGap = 0;
    this.properties.push(new Property(this, "accentGap", accentGap, {
        get: function() {
            return accentGap;
        },
        set: function(value) {
            accentGap = value;
        },
        compute: function() {
            var accentGapVal = 0.25;
            if (this.accentContainerCharacter !== "") {
                if (this.equation.fontMetrics.shortCharacters.contains(this.accentContainerCharacter)) {
                    accentGapVal = -0.02;
                } else if (this.equation.fontMetrics.mediumCharacters.contains(this.accentContainerCharacter)) {
                    accentGapVal = 0.135;
                } else if (this.equation.fontMetrics.tallCharacters.contains(this.accentContainerCharacter)) {
                    accentGapVal = 0.22;
                }
            }
            return accentGapVal;
        },
        updateDom: function() {}
    }));

    var accentContainerCharacter = ""
    // Set up the accentContainerCharacter calculation
    var accentContainerCharacter = 0;
    this.properties.push(new Property(this, "accentContainerCharacter", accentContainerCharacter, {
        get: function() {
            return accentContainerCharacter;
        },
        set: function(value) {
            accentContainerCharacter = value;
        },
        compute: function() {
            var accentContainerCharacterVal = "";
            if (this.accentContainer.wrappers.length > 0) {
                if (this.accentContainer.wrappers.length === 1) {
                    if (this.accentContainer.wrappers[0] instanceof eqEd.SymbolWrapper) {
                        // replace i/j with imath/jmath if hat accent.
                        if (this.accentSymbol.character === '^') {
                            var symbol = this.accentContainer.wrappers[0].symbol;
                            if (symbol.character === 'i') {
                                symbol.character = 'ı';
                                symbol.fontStyle = 'MathJax_MainItalic';
                                symbol.domObj = symbol.buildDomObj();
                                symbol.parent.domObj.empty();
                                symbol.parent.domObj.append(symbol.domObj);
                            } else if (symbol.character === 'j') {
                                symbol.character = 'ȷ';
                                symbol.fontStyle = 'MathJax_MainItalic';
                                symbol.domObj = symbol.buildDomObj();
                                symbol.parent.domObj.empty();
                                symbol.parent.domObj.append(symbol.domObj);
                            }
                        }
                        accentContainerCharacterVal = this.accentContainer.wrappers[0].symbol.character;
                    } else if (this.accentContainer.wrappers[0] instanceof eqEd.SquareEmptyContainerWrapper) {
                        accentContainerCharacterVal = "squareEmptyContainerWrapper";
                    }
                } else {
                    accentContainerCharacterVal = "multipleWrappers";
                }
            }
            return accentContainerCharacterVal;
        },
        updateDom: function() {}
    }));


    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var containerWidth = this.accentContainer.width;
            return containerWidth;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));

    // Set up the topAlign calculation
    var topAlign = 0;
    this.properties.push(new Property(this, "topAlign", topAlign, {
        get: function() {
            return topAlign;
        },
        set: function(value) {
            topAlign = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.fontSize];
            var topAlignVal = 0;
            if (this.accentContainer.wrappers.length > 0) {
                topAlignVal = this.accentContainer.wrappers[this.accentContainer.maxTopAlignIndex].topAlign;
                if (this.accentGap >= 0) {
                    topAlignVal += this.accentGap * fontHeight;
                }
            }
            return topAlignVal;
        },
        updateDom: function() {}
    }));

    // Set up the bottomAlign calculation
    var bottomAlign = 0;
    this.properties.push(new Property(this, "bottomAlign", bottomAlign, {
        get: function() {
            return bottomAlign;
        },
        set: function(value) {
            bottomAlign = value;
        },
        compute: function() {
            var bottomAlignVal = 0;
            if (this.accentContainer.wrappers.length > 0) {
                bottomAlignVal = this.accentContainer.wrappers[this.accentContainer.maxBottomAlignIndex].bottomAlign;
            }
            return bottomAlignVal;
        },
        updateDom: function() {}
    }));
};
(function() {
    // subclass extends superclass
    eqEd.AccentWrapper.prototype = Object.create(eqEd.Wrapper.prototype);
    eqEd.AccentWrapper.prototype.constructor = eqEd.AccentWrapper;
    eqEd.AccentWrapper.prototype.buildDomObj = function() {
        return new eqEd.WrapperDom(this,
            '<div class="eqEdWrapper accentWrapper"></div>')
    };
    eqEd.AccentWrapper.prototype.clone = function() {
        var copy = new this.constructor(this.equation, this.character, this.fontStyle);
        copy.accentSymbol = new eqEd.AccentSymbol(copy, this.character, this.fontStyle);
        copy.accentContainer = this.accentContainer.clone();
        copy.accentContainer.parent = copy;
        copy.domObj = copy.buildDomObj();
        copy.domObj.append(copy.accentSymbol.domObj);
        copy.domObj.append(copy.accentContainer.domObj);
        
        copy.childNoncontainers = [copy.accentSymbol];
        copy.childContainers = [copy.accentContainer];

        return copy;
    };
    eqEd.AccentWrapper.prototype.buildJsonObj = function() {
        var jsonObj = {
            type: this.className.substring(5, this.className.length - 7),
            value: this.character,
            operands: {
                accentedExpression: this.accentContainer.buildJsonObj()
            }
        };
        return jsonObj;
    };
    eqEd.AccentWrapper.constructFromJsonObj = function(jsonObj, equation) {
      var accentWrapper = new eqEd.AccentWrapper(equation, jsonObj.value, 'MathJax_Main');
      for (var i = 0; i < jsonObj.operands.accentedExpression.length; i++) {
        var innerWrapperCtor = eqEd.Equation.JsonTypeToConstructor(jsonObj.operands.accentedExpression[i].type);
        var innerWrapper = innerWrapperCtor.constructFromJsonObj(jsonObj.operands.accentedExpression[i], equation);
        accentWrapper.accentContainer.addWrappers([i, innerWrapper]);
      }
      return accentWrapper;
    }
})();

/* End eq/js/equation-components/wrappers/accentWrapper.js*/

/* Begin eq/js/equation-components/misc/accentSymbol.js*/

eqEd.AccentSymbol = function(parent, character, fontStyle) {
    eqEd.Symbol.call(this, parent, character, fontStyle); // call super constructor.
    this.className = "eqEd.AccentSymbol";

    // width has already been added to 
    // properties in superclass needs removed to be overriden
    for(var i = 0; i < this.properties.length; i++) {
        if (this.properties[i].propName === "width"
            || this.properties[i].propName === "left"
            || this.properties[i].propName === "top") {
            this.properties.splice(i, 1);
        }
    }

    var adjustLeftByChar = {
        'squareEmptyContainerWrapper': {
            '˙': 0,
            '^': 0,
            '⃗': 0.25,
            '¯': 0
        },
        'multipleWrappers': {
            '˙': 0,
            '^': 0,
            '⃗': 0.25,
            '¯': 0
        },
        'a': {
            '˙': 0,
            '^': 0,
            '⃗': 0.25,
            '¯': 0
        },
        'c': {
            '˙': 0.06,
            '^': 0.06,
            '⃗': 0.3,
            '¯': 0.025
        }, 
        'e': {
            '˙': 0.06,
            '^': 0.06,
            '⃗': 0.3,
            '¯': 0.025
        }, 
        'g': {
            '˙': 0.06,
            '^': 0.06,
            '⃗': 0.3,
            '¯': 0.025
        },
        'ı': {
            '˙': 0.04,
            '^': 0.035,
            '⃗': 0.3,
            '¯': 0.025
        },
        'ȷ': {
            '˙': 0.075,
            '^': 0.04,
            '⃗': 0.3,
            '¯': 0.025
        },
        'm': {
            '˙': 0,
            '^': 0,
            '⃗': 0.3,
            '¯': 0.025
        },
        'n': {
            '˙': 0,
            '^': 0,
            '⃗': 0.3,
            '¯': 0.025
        },
        'o': {
            '˙': 0.04,
            '^': 0.04,
            '⃗': 0.3,
            '¯': 0.06
        },
        'p': {
            '˙': 0.075,
            '^': 0.075,
            '⃗': 0.3,
            '¯': 0.025
        },
        'q': {
            '˙': 0,
            '^': 0,
            '⃗': 0.3,
            '¯': 0.025
        },
        'r': {
            '˙': 0.06,
            '^': 0.06,
            '⃗': 0.3,
            '¯': 0.025
        },
        's': {
            '˙': 0.05,
            '^': 0.05,
            '⃗': 0.3,
            '¯': 0.025
        },
        'u': {
            '˙': 0.025,
            '^': 0.025,
            '⃗': 0.3,
            '¯': 0.025
        },
        'v': {
            '˙': 0.035,
            '^': 0.035,
            '⃗': 0.3,
            '¯': 0.025
        },
        'w': {
            '˙': 0.1,
            '^': 0.1,
            '⃗': 0.3,
            '¯': 0.025
        },
        'x': {
            '˙': 0.02,
            '^': 0.02,
            '⃗': 0.3,
            '¯': 0.025
        },
        'y': {
            '˙': 0.065,
            '^': 0.065,
            '⃗': 0.3,
            '¯': 0.025
        },
        'z': {
            '˙': 0.05,
            '^': 0.05,
            '⃗': 0.3,
            '¯': 0.05
        },
        'α': {
            '˙': 0.02,
            '^': 0.02,
            '⃗': 0.3,
            '¯': 0.025
        },
        'γ': {
            '˙': 0.05,
            '^': 0.05,
            '⃗': 0.3,
            '¯': 0.025
        },
        'ε': {
            '˙': 0,
            '^': 0,
            '⃗': 0.3,
            '¯': 0.025
        },
        'ϵ': {
            '˙': 0.055,
            '^': 0.055,
            '⃗': 0.3,
            '¯': 0.025
        },
        'η': {
            '˙': 0.075,
            '^': 0.075,
            '⃗': 0.3,
            '¯': 0.025
        },
        'ι': {
            '˙': 0.03,
            '^': 0,
            '⃗': 0.3,
            '¯': 0.025
        },
        'κ': {
            '˙': 0,
            '^': 0,
            '⃗': 0.3,
            '¯': 0.025
        },
        'μ': {
            '˙': 0.05,
            '^': 0.05,
            '⃗': 0.3,
            '¯': 0.025
        },
        'ν': {
            '˙': 0.055,
            '^': 0.055,
            '⃗': 0.3,
            '¯': 0.025
        },
        'π': {
            '˙': 0,
            '^': 0,
            '⃗': 0.3,
            '¯': 0.025
        },
        'ϖ': {
            '˙': 0,
            '^': 0,
            '⃗': 0.3,
            '¯': 0.025
        },
        'ρ': {
            '˙': 0.055,
            '^': 0.055,
            '⃗': 0.3,
            '¯': 0.065
        },
        'ϱ': {
            '˙': 0.06,
            '^': 0.06,
            '⃗': 0.3,
            '¯': 0.065
        },
        'σ': {
            '˙': 0,
            '^': 0,
            '⃗': 0.3,
            '¯': 0.025
        },
        'ς': {
            '˙': 0,
            '^': 0,
            '⃗': 0.3,
            '¯': 0.025
        },
        'τ': {
            '˙': 0,
            '^': 0,
            '⃗': 0.3,
            '¯': 0.025
        },
        'υ': {
            '˙': 0.05,
            '^': 0.05,
            '⃗': 0.3,
            '¯': 0.025
        },
        'φ': {
            '˙': 0,
            '^': 0,
            '⃗': 0.3,
            '¯': 0.025
        },
        'χ': {
            '˙': 0,
            '^': 0,
            '⃗': 0.3,
            '¯': 0.025
        },
        'ω': {
            '˙': 0.035,
            '^': 0.035,
            '⃗': 0.3,
            '¯': 0.025
        },
        'i': {
            '˙': 0.04,
            '^': 0,
            '⃗': 0.3,
            '¯': 0.025
        },
        'j': {
            '˙': 0.075,
            '^': 0.04,
            '⃗': 0.4,
            '¯': 0.135
        },
        't': {
            '˙': 0.075,
            '^': 0.035,
            '⃗': 0.325,
            '¯': 0.025
        },
        'b': {
            '˙': 0,
            '^': 0,
            '⃗': 0.25,
            '¯': 0
        },
        'd': {
            '˙': 0.18,
            '^': 0.18,
            '⃗': 0.45,
            '¯': 0.165
        },
        'f': {
            '˙': 0.18,
            '^': 0.18,
            '⃗': 0.45,
            '¯': 0.165
        },
        'h': {
            '˙': -0.05,
            '^': -0.05,
            '⃗': 0.25,
            '¯': -0.05
        },
        'k': {
            '˙': 0,
            '^': 0,
            '⃗': 0.25,
            '¯': -0.05
        },
        'l': {
            '˙': 0,
            '^': 0,
            '⃗': 0.3,
            '¯': 0.025
        },
        'A': {
            '˙': 0.15,
            '^': 0.15,
            '⃗': 0.45,
            '¯': 0.15
        },
        'B': {
            '˙': 0.08,
            '^': 0.08,
            '⃗': 0.35,
            '¯': 0.075
        },
        'C': {
            '˙': 0.125,
            '^': 0.125,
            '⃗': 0.35,
            '¯': 0.125
        },
        'D': {
            '˙': 0.025,
            '^': 0.025,
            '⃗': 0.35,
            '¯': 0.075
        },
        'E': {
            '˙': 0.1,
            '^': 0.1,
            '⃗': 0.35,
            '¯': 0.075
        },
        'F': {
            '˙': 0.15,
            '^': 0.15,
            '⃗': 0.35,
            '¯': 0.075
        },
        'G': {
            '˙': 0.1,
            '^': 0.1,
            '⃗': 0.35,
            '¯': 0.125
        },
        'H': {
            '˙': 0.08,
            '^': 0.08,
            '⃗': 0.35,
            '¯': 0.075
        },
        'I': {
            '˙': 0.085,
            '^': 0.085,
            '⃗': 0.35,
            '¯': 0.1
        },
        'J': {
            '˙': 0.175,
            '^': 0.175,
            '⃗': 0.45,
            '¯': 0.165
        },
        'K': {
            '˙': 0.1,
            '^': 0.1,
            '⃗': 0.35,
            '¯': 0.075
        },
        'L': {
            '˙': 0.025,
            '^': 0.025,
            '⃗': 0.3,
            '¯': 0.0125
        },
        'M': {
            '˙': 0.08,
            '^': 0.08,
            '⃗': 0.35,
            '¯': 0.075
        },
        'N': {
            '˙': 0.08,
            '^': 0.08,
            '⃗': 0.35,
            '¯': 0.075
        },
        'O': {
            '˙': 0.075,
            '^': 0.075,
            '⃗': 0.35,
            '¯': 0.125
        },
        'P': {
            '˙': 0.1,
            '^': 0.1,
            '⃗': 0.35,
            '¯': 0.075
        },
        'Q': {
            '˙': 0.075,
            '^': 0.075,
            '⃗': 0.35,
            '¯': 0.125
        },
        'R': {
            '˙': 0.075,
            '^': 0.075,
            '⃗': 0.35,
            '¯': 0.075
        },
        'S': {
            '˙': 0.1,
            '^': 0.1,
            '⃗': 0.4,
            '¯': 0.075
        },
        'T': {
            '˙': 0.05,
            '^': 0.05,
            '⃗': 0.3,
            '¯': 0.025
        },
        'U': {
            '˙': 0.05,
            '^': 0.05,
            '⃗': 0.3,
            '¯': 0.075
        },
        'V': {
            '˙': 0.05,
            '^': 0.05,
            '⃗': 0.3,
            '¯': 0.075
        },
        'W': {
            '˙': 0.035,
            '^': 0.035,
            '⃗': 0.3,
            '¯': 0.025
        },
        'X': {
            '˙': 0.08,
            '^': 0.08,
            '⃗': 0.35,
            '¯': 0.075
        },
        'Y': {
            '˙': 0.045,
            '^': 0.045,
            '⃗': 0.3,
            '¯': 0.025
        },
        'Z': {
            '˙': 0.125,
            '^': 0.125,
            '⃗': 0.375,
            '¯': 0.1
        },
        'β': {
            '˙': 0.1,
            '^': 0.1,
            '⃗': 0.35,
            '¯': 0.1
        },
        'δ': {
            '˙': 0.06,
            '^': 0.06,
            '⃗': 0.35,
            '¯': 0.08
        },
        'ζ': {
            '˙': 0.1,
            '^': 0.1,
            '⃗': 0.35,
            '¯': 0.08
        },
        'θ': {
            '˙': 0.055,
            '^': 0.055,
            '⃗': 0.4,
            '¯': 0.08
        },
        'ϑ': {
            '˙': 0.075,
            '^': 0.075,
            '⃗': 0.4,
            '¯': 0.08
        },
        'λ': {
            '˙': -0.035,
            '^': -0.035,
            '⃗': 0.25,
            '¯': -0.025
        },
        'ξ': {
            '˙': 0.125,
            '^': 0.125,
            '⃗': 0.35,
            '¯': 0.08
        },
        'ϕ': {
            '˙': 0.115,
            '^': 0.115,
            '⃗': 0.4,
            '¯': 0.125
        },
        'ψ': {
            '˙': 0.115,
            '^': 0.115,
            '⃗': 0.4,
            '¯': 0.125
        },
        'Γ': {
            '˙': 0,
            '^': 0,
            '⃗': 0.275,
            '¯': 0
        },
        'Δ': {
            '˙': 0,
            '^': 0,
            '⃗': 0.275,
            '¯': 0
        },
        'Θ': {
            '˙': 0,
            '^': 0,
            '⃗': 0.275,
            '¯': 0.015
        },
        'Λ': {
            '˙': 0,
            '^': 0,
            '⃗': 0.275,
            '¯': 0
        },
        'Ξ': {
            '˙': 0,
            '^': 0,
            '⃗': 0.25,
            '¯': 0
        },
        'Π': {
            '˙': 0,
            '^': 0,
            '⃗': 0.25,
            '¯': 0
        },
        'Σ': {
            '˙': 0,
            '^': 0,
            '⃗': 0.275,
            '¯': 0
        },
        'Υ': {
            '˙': 0,
            '^': 0,
            '⃗': 0.275,
            '¯': 0
        },
        'Φ': {
            '˙': 0,
            '^': 0,
            '⃗': 0.275,
            '¯': 0
        },
        'Ψ': {
            '˙': 0,
            '^': 0,
            '⃗': 0.275,
            '¯': 0
        },
        'Ω': {
            '˙': 0,
            '^': 0,
            '⃗': 0.275,
            '¯': 0
        }
    }

    this.domObj.addClass('accentSymbol');
    if (this.character === '˙') {
        if (IEVersion >= 9) {
            this.domObj.addClass('dotAccentIE');
        } else {
            this.domObj.addClass('dotAccent');
        }
    } else if (this.character === '^') {
        if (IEVersion >= 9) {
            this.domObj.addClass('hatAccentIE');
        } else {
            this.domObj.addClass('hatAccent');
        }
    } else if (this.character === '⃗') {
        this.domObj.addClass('vectorAccent');
    } else if (this.character === '¯') {
        if (IEVersion >= 9) {
            this.domObj.addClass('barAccentIE');
        } else {
            this.domObj.addClass('barAccent');
        }
    }


    // Set up the adjustLeft calculation
    var adjustLeft = 0;
    this.properties.push(new Property(this, "adjustLeft", adjustLeft, {
        get: function() {
            return adjustLeft;
        },
        set: function(value) {
            adjustLeft = value;
        },
        compute: function() {
            var adjustLeftVal = 0;
            if (typeof adjustLeftByChar[this.parent.accentContainerCharacter] !== "undefined") {
                adjustLeftVal = adjustLeftByChar[this.parent.accentContainerCharacter][this.character];
            }
            return adjustLeftVal;
        },
        updateDom: function() {}
    }));

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var fontWidth = this.equation.fontMetrics.width[this.character][this.fontStyle][this.parent.parent.fontSize];
            var leftOffset = 0.5 * (this.parent.width - (this.parent.padLeft + this.parent.padRight) * fontHeight - fontWidth);
            return leftOffset;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var topVal = 0
            if (this.parent.accentGap < 0) {
                topVal = -1 * this.parent.accentGap * fontHeight;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the width calculation
    var width = 0;
    this.properties.push(new Property(this, "width", width, {
        get: function() {
            return width;
        },
        set: function(value) {
            width = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var widthVal = 0;
            if (this.character === '˙') {
                widthVal = 0.33 * fontHeight;
            } else if (this.character === '^') {
                widthVal = 0.4 * fontHeight;
            } else if (this.character === '⃗') {
                widthVal = 1;
            }
            return widthVal;
        },
        updateDom: function() {
            this.domObj.updateWidth(this.width);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.AccentSymbol.prototype = Object.create(eqEd.Symbol.prototype);
    eqEd.AccentSymbol.prototype.constructor = eqEd.AccentSymbol;
})();

/* End eq/js/equation-components/misc/accentSymbol.js*/

/* Begin eq/js/equation-components/containers/accentContainer.js*/

eqEd.AccentContainer = function(parent) {
    eqEd.Container.call(this, parent);
    this.className = "eqEd.AccentContainer";
    this.domObj = this.buildDomObj();
    var squareEmptyContainerWrapper = new eqEd.SquareEmptyContainerWrapper(this.equation);
    this.addWrappers([0, squareEmptyContainerWrapper]);

    // Set up the left calculation
    var left = 0;
    this.properties.push(new Property(this, "left", left, {
        get: function() {
            return left;
        },
        set: function(value) {
            left = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var leftOffset = 0.5 * (this.parent.width - (this.parent.padLeft + this.parent.padRight) * fontHeight - this.width);
            return 0;
        },
        updateDom: function() {
            this.domObj.updateLeft(this.left);
        }
    }));

    // Set up the top calculation
    var top = 0;
    this.properties.push(new Property(this, "top", top, {
        get: function() {
            return top;
        },
        set: function(value) {
            top = value;
        },
        compute: function() {
            var fontHeight = this.equation.fontMetrics.height[this.parent.parent.fontSize];
            var topVal = 0
            if (this.parent.accentGap >= 0) {
                topVal = this.parent.accentGap * fontHeight;
            }
            return topVal;
        },
        updateDom: function() {
            this.domObj.updateTop(this.top);
        }
    }));

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            var fontSizeVal = "";
            var actualParentContainer = this.parent.parent;
            while (actualParentContainer instanceof eqEd.BracketContainer) {
                actualParentContainer = actualParentContainer.parent.parent;
            }
            return actualParentContainer.fontSize;
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};
(function() {
    // subclass extends superclass
    eqEd.AccentContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.AccentContainer.prototype.constructor = eqEd.AccentContainer;
    eqEd.AccentContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer accentContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/accentContainer.js*/

/* Begin eq/js/blinkingCursor.js*/

var toggleCursorVisibility = function() {
    $('.cursor').toggleClass('cursorOff');
}

var cursorBlinkTimers = new Array();

var addBlink = function() {
    removeBlink();
    // cause a delay before setting the 
    (function() {
        window.setTimeout(function() { }, 3000);
    })();
    var intervalId = window.setInterval(toggleCursorVisibility, 750);

    cursorBlinkTimers.push(intervalId);
}

var removeBlink = function() {
    for (var i = 0; i < cursorBlinkTimers.length; i++) {
        window.clearInterval(cursorBlinkTimers[i]);
    }
    cursorBlinkTimers = [];
    $('.cursorOff').removeClass('cursorOff');
}

/* End eq/js/blinkingCursor.js*/

/* Begin eq/js/equation-components/containers/topLevelContainer.js*/

eqEd.TopLevelContainer = function(parent) {
    eqEd.Container.call(this, parent);
    this.className = "eqEd.TopLevelContainer";

    this.equation = parent;
    this.padTop = 0.2;
    this.padBottom = 0.2;
    this.domObj = this.buildDomObj();
    var topLevelEmptyContainerWrapper = new eqEd.TopLevelEmptyContainerWrapper(this.equation);
    this.addWrappers([0, topLevelEmptyContainerWrapper]);

    // Set up the fontSize calculation
    var fontSize = "";
    this.properties.push(new Property(this, "fontSize", fontSize, {
        get: function() {
            return fontSize;
        },
        set: function(value) {
            fontSize = value;
        },
        compute: function() {
            return "fontSizeNormal";
        },
        updateDom: function() {
            this.domObj.updateFontSize(this.fontSize);
        }
    }));
};

(function() {
    // subclass extends superclass
    eqEd.TopLevelContainer.prototype = Object.create(eqEd.Container.prototype);
    eqEd.TopLevelContainer.prototype.constructor = eqEd.TopLevelContainer;
    eqEd.TopLevelContainer.prototype.buildDomObj = function() {
        return new eqEd.ContainerDom(this,
            '<div class="eqEdContainer topLevelContainer"></div>');
    };
})();

/* End eq/js/equation-components/containers/topLevelContainer.js*/

/* Begin eq/js/mouseInteraction.js*/

var mouseDown = false;
var toggleLines = [];
var highlightStartIndex = null;
var highlightEndIndex = null;

var removeCursor = function() {
    $('.cursor').remove();
    removeBlink();
}

var removeHighlight = function() {
    $('.highlight').remove();
    $('.highlighted').removeClass('highlighted');
    $('.eqEdContainer').css('z-index', 3);
    $('.eqEdWrapper').css('z-index', 3);
    toggleLines = [];
    highlightStartIndex = null;
}

var clearOnMouseDown = function() {
    mouseDown = true;
    removeCursor();
    removeHighlight();
    clearHighlighted();
    $('.activeContainer').removeClass('activeContainer');
    $('.hoverContainer').removeClass('hoverContainer');
    $('#hiddenFocusInput').blur();
};

var moveHiddenInput = function() {
    $('#hiddenFocusInput').css('left', $('.cursor').first().offset().left);
    $('#hiddenFocusInput').css('top', $('.cursor').first().offset().top);
    $('#hiddenFocusInput').focus().click();
};

var calculateIndex = function(offsetLeft) {
    var index = 0;
    var indexSet= false;
    for (var i = 0; i < toggleLines.length; i++) {
        if (offsetLeft < toggleLines[i]) {
            index = i;
            indexSet = true;
            break;
        }
    }
    if (!indexSet) {
        index = toggleLines.length;
    }
    return index;
};

// side effect: populates toggleLines array, and highlightStartIndex.
var addCursor = function(container, characterClickPos) {
    removeCursor();
    $('.activeContainer').removeClass('activeContainer');
    if (!(container instanceof eqEd.TopLevelContainer)) {
        container.domObj.value.addClass('activeContainer');
    }
    var cursor;
    if (container instanceof eqEd.SquareEmptyContainer) {
        cursor = $('<div class="cursor squareCursor"></div>');
    } else {
        var cumulative = 0;
        var cursorLeft = -1;
        var cursorLeftSet = false;
        var toggleLinesEmpty = (toggleLines.length === 0);
        if (!(container.wrappers[0] instanceof eqEd.TopLevelEmptyContainerWrapper)) {
            for (var i = 0; i < container.wrappers.length; i++) {
                var wrapper = container.wrappers[i];
                cumulative += 0.5 * wrapper.width;
                if (toggleLinesEmpty) {
                    toggleLines.push(cumulative);
                }
                if (characterClickPos < cumulative && !cursorLeftSet) {
                    // - 1 because cursor has a width of 2
                    cursorLeft += cumulative - 0.5 * wrapper.width;
                    highlightStartIndex = i;
                    cursorLeftSet = true;
                }
                cumulative += 0.5 * wrapper.width;
                
            }
            if (!cursorLeftSet) {
                cursorLeft += cumulative;
                highlightStartIndex = container.wrappers.length;
            }
        }
        cursor = $('<div class="cursor normalCursor"></div>');
        cursor.css('left', cursorLeft);
    }
    container.domObj.value.append(cursor);
    addBlink();
    moveHiddenInput();
};

// side effect: populates toggleLines array, and highlightStartIndex.
var addCursorAtIndex = function(container, index) {
    removeCursor();
    removeHighlight();
    addHighlight(container);
    $('.activeContainer').removeClass('activeContainer');
    if (!(container instanceof eqEd.TopLevelContainer)) {
        container.domObj.value.addClass('activeContainer');
    }
    var cursor;
    highlightStartIndex = index;
    if (container instanceof eqEd.SquareEmptyContainer) {
        cursor = $('<div class="cursor squareCursor"></div>');
    } else {
        var cumulative = 0;
        var cursorLeft = -1;
        var cursorLeftSet = false;
        var toggleLinesEmpty = (toggleLines.length === 0);
        if (!(container.wrappers[0] instanceof eqEd.TopLevelEmptyContainerWrapper)) {
            for (var i = 0; i < container.wrappers.length; i++) {
                var wrapper = container.wrappers[i];
                if (index === i) {
                    cursorLeft += cumulative;
                    cursorLeftSet = true;
                }
                cumulative += 0.5 * wrapper.width;
                if (toggleLinesEmpty) {
                    toggleLines.push(cumulative);
                }
                cumulative += 0.5 * wrapper.width;
            }
        }
        if (!cursorLeftSet) {
            cursorLeft += cumulative;
            cursorLeftSet = true;
        }
        cursor = $('<div class="cursor normalCursor"></div>');
        cursor.css('left', cursorLeft);
    }
    container.domObj.value.append(cursor);
    addBlink();
    moveHiddenInput();
};

var addHighlight = function(container) {
    var highlight = $('<div class="highlight"></div>');
    container.domObj.value.css('z-index', 4);
    highlight.css('z-index', 5);
    container.domObj.value.children().css('z-index', 6);
    container.domObj.value.append(highlight);
};

var updateHighlightFormatting = function(container, endIndex) {
    highlightEndIndex = endIndex;
    var highlight = $('.highlight');
    if (highlight.length > 0) {
        var left = 0;
        var top = 0;
        var height = 0;
        $('.highlighted').removeClass('highlighted');
        if (highlightStartIndex < highlightEndIndex) {
            left = container.wrappers[highlightStartIndex].left;
            top = 0;
            height = container.height;
            var widthSum = 0;
            for (var i = highlightStartIndex; i < highlightEndIndex; i++) {
                if (highlightStartIndex !== highlightEndIndex) {
                    var wrapper = container.wrappers[i];
                    wrapper.domObj.value.addClass('highlighted');
                    widthSum += wrapper.width;
                }
            }
            width = widthSum;
        } else if (highlightStartIndex > highlightEndIndex) {
            left = container.wrappers[highlightEndIndex].left;
            top = 0;
            height = container.height;

            var widthSum = 0;
            for (var i = highlightEndIndex; i < highlightStartIndex; i++) {
                if (highlightStartIndex !== highlightEndIndex) {
                    var wrapper = container.wrappers[i];
                    wrapper.domObj.value.addClass('highlighted');
                    widthSum += wrapper.width;
                }
            }
            width = widthSum;
        } else if (highlightStartIndex === highlightEndIndex) {
            left = 0;
            top = 0;
            height = 0;
            width = 0;
        }
        highlight.css({
            left: left,
            top: top,
            height: height,
            width: width
        });
    }
}

$(document).on('touchstart mousedown', function(e) {
    clearOnMouseDown();
});

$(document).on('touchend mouseup', function(e) {
    mouseDown = false;
    if ($('.cursor').length > 0) {
        addBlink();
    }
});

var onMouseDown = function(self, e) {
    if (!$(self).children().first().hasClass('squareEmptyContainerWrapper')) {
        e.preventDefault();
        e.stopPropagation();
        clearOnMouseDown();
        var container = $(self).data("eqObject");
        addHighlight(container);
        var xOffset = (typeof e.originalEvent.pageX !== 'undefined') ? e.originalEvent.pageX : e.originalEvent.touches[0].pageX;
        var characterClickPos = xOffset - container.domObj.value.offset().left;
        addCursor(container, characterClickPos);
    }
}

$(document).on('touchstart mousedown', '.tabs', function(e) {
    e.stopPropagation();
});

$(document).on('touchstart mousedown', '.eqEdContainer', function(e) {
    onMouseDown(this, e);
});

$(document).on('mousemove', function(e) {
    $('.hoverContainer').removeClass('hoverContainer');
});

$(document).on('mousemove', '.eqEdContainer', function(e) {
    if (mouseDown) {
        clearHighlighted();
    }

    if (mouseDown 
        && !$(this).children().first().hasClass('squareEmptyContainerWrapper') 
        && !$(this).hasClass('squareEmptyContainer')) {
        var container = $(this).data("eqObject");
        if (highlightStartIndex !== null && container.domObj.value.children('.highlight').length > 0) {
            var characterClickPos = e.pageX - container.domObj.value.offset().left;
            var index = calculateIndex(characterClickPos);
            updateHighlightFormatting(container, index);
            if (highlightStartIndex === index) {
                addCursorAtIndex(container, index);
            } else {
                removeCursor();
            }
        }
    } else {
        var container = $(this).data("eqObject");
        $('.hoverContainer').removeClass('hoverContainer');
        if (!($(this).hasClass('activeContainer')) 
         && !(container.wrappers[0] instanceof eqEd.SquareEmptyContainerWrapper)
         && !$(this).hasClass('topLevelContainer')
         && !($('.highlighted').length > 0)) {
            $(this).addClass('hoverContainer');
        }
        e.preventDefault();
        e.stopPropagation();
    }
})

$(document).on('mouseenter', '.eqEdContainer', function (e) {
    e.preventDefault();
    e.stopPropagation();
    if (mouseDown) {
        clearHighlighted();
        if (highlightStartIndex === null) {
            onMouseDown(this, e);
        } else {
            $(this).trigger("mousemove");
        }
    }
});

$(document).on('mouseleave', '.eqEdContainer', function (e) {
    e.preventDefault();
    e.stopPropagation();
});


/* End eq/js/mouseInteraction.js*/

/* Begin eq/js/addWrapperUtil.js*/

var insertWrapper = function(wrapper) {
    var cursor = $('.cursor');
    var highlighted = $('.highlighted');
    if (cursor.length > 0) {
        var container = cursor.parent().data('eqObject');
        if (cursor.parent().hasClass('squareEmptyContainer')) {
            container = container.parent.parent;
        }
        container.addWrappers([highlightStartIndex, wrapper]);
        wrapper.updateAll();
        removeCursor();
        if (wrapper.childContainers.length > 0) {
            if (wrapper.childContainers[0].wrappers[0] instanceof eqEd.EmptyContainerWrapper) {
                addCursorAtIndex(wrapper.childContainers[0].wrappers[0].childContainers[0], 0);
                container = wrapper.childContainers[0].wrappers[0].childContainers[0];
            } else {
                addCursorAtIndex(wrapper.childContainers[0], wrapper.childContainers[0].wrappers.length);
            }
        } else {
            addCursorAtIndex(container, (++highlightStartIndex));
        }
    } else if (highlighted.length > 0) {
        var container = highlighted.parent().data('eqObject');
        var deleteWrappers;
        if (highlightStartIndex < highlightEndIndex) {
            deleteWrappers = _.range(highlightStartIndex, highlightEndIndex);
        } else {
            deleteWrappers = _.range(highlightEndIndex, highlightStartIndex);
        }
        if (wrapper.childContainers.length > 0) {
            container.addWrappers([deleteWrappers[0], wrapper]);
            removeCursor();
            removeHighlight();
            var copiedWrappers = [];
            for (var i = 0; i < deleteWrappers.length; i++) {
                var deleteWrapperIndex = deleteWrappers[i] + 1;
                var deleteWrapper = container.wrappers[deleteWrapperIndex];
                var copiedWrapper = deleteWrapper.clone();
                copiedWrappers.push([i, copiedWrapper]);
            }
            eqEd.Container.prototype.removeWrappers.apply(container, _.map(deleteWrappers, function(num){ return num + 1; }));
            eqEd.Container.prototype.addWrappers.apply(wrapper.childContainers[0], copiedWrappers);
            container.updateAll();
            addCursorAtIndex(wrapper.childContainers[0], copiedWrappers.length);
        } else {
            eqEd.Container.prototype.removeWrappers.apply(container, deleteWrappers);
            container.updateAll();
            highlightStartIndex = (highlightStartIndex < highlightEndIndex) ? highlightStartIndex : highlightEndIndex;
            updateHighlightFormatting(container, highlightStartIndex);
            addCursorAtIndex(container, highlightStartIndex);
            insertWrapper(wrapper);
        }
    }
};

/* End eq/js/addWrapperUtil.js*/

/* Begin eq/js/keyboardInteraction.js*/

var setupKeyboardEvents = function() {
    var MathJax_MathItalic = [
        'q',
        'w',
        'e',
        'r',
        't',
        'y',
        'u',
        'i',
        'o',
        'p',
        'a',
        's',
        'd',
        'f',
        'g',
        'h',
        'j',
        'k',
        'l',
        'z',
        'x',
        'c',
        'v',
        'b',
        'n',
        'm',
        'Q',
        'W',
        'E',
        'R',
        'T',
        'Y',
        'U',
        'I',
        'O',
        'P',
        'A',
        'S',
        'D',
        'F',
        'G',
        'H',
        'J',
        'K',
        'L',
        'Z',
        'X',
        'C',
        'V',
        'B',
        'N',
        'M'
    ];
    var MathJax_Main = [
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        '0',
        '!',
        '$',
        '%',
        '.'
    ];

    var operatorCharacters = [
        '*',
        '-',
        '=',
        '+',
        '/',
        '<',
        '>',
        ','
    ];
    var operatorCharactersMap = {
        '-': '−',
        '/': '÷',
        '*': '⋅',
        '=': '=',
        '+': '+',
        '<': '<',
        '>': '>',
        ',': ','
    }
    var bracketCharacters = [
        '(',
        ')',
        '[',
        ']',
        '{',
        '}',
        '|'
    ];
    var bracketCharactersMap = {
        '(': "leftParenthesisBracket",
        ')': "rightParenthesisBracket",
        '[': "leftSquareBracket",
        ']': "rightSquareBracket",
        '{': "leftCurlyBracket",
        '}': "rightCurlyBracket",
        '|': null
    }

    $(document).on('keypress', function(e) {
        if ($('.cursor').length > 0 || $('.highlight').length > 0) {
            var equation = null;
            if ($('.cursor').length > 0) {
                equation = $('.cursor').parent().data('eqObject').equation;
            } else {
                equation = $('.highlight').parent().data('eqObject').equation;
            }  
            var character = String.fromCharCode(e.which);
            if ($.inArray(character, MathJax_MathItalic) > -1) {
                var symbolWrapper = new eqEd.SymbolWrapper(equation, character, "MathJax_MathItalic");
                insertWrapper(symbolWrapper);
                return false;
            } else if ($.inArray(character, MathJax_Main) > -1) {
                var symbolWrapper = new eqEd.SymbolWrapper(equation, character, "MathJax_Main");
                insertWrapper(symbolWrapper);
                return false;
            } else if ($.inArray(character, operatorCharacters) > -1) {
                var operatorWrapper = new eqEd.OperatorWrapper(equation, operatorCharactersMap[character], "MathJax_Main");
                insertWrapper(operatorWrapper);
                return false;
            } else if ($.inArray(character, bracketCharacters) > -1) {
                var bracketWrapper = new eqEd.BracketWrapper(equation, bracketCharactersMap[character]);
                insertWrapper(bracketWrapper);
                return false;
            } else if (character === '\\') {
                // setminus
                var operatorWrapper = new eqEd.OperatorWrapper(equation, '∖', "MathJax_Main");
                insertWrapper(operatorWrapper);
                return false;
            } else if (character === ':') {
                // colon
                var operatorWrapper = new eqEd.OperatorWrapper(equation, ':', "MathJax_Main");
                insertWrapper(operatorWrapper);
                return false;
            } else if (character === '\'') {
                // apostrophe
                var operatorWrapper = new eqEd.OperatorWrapper(equation, '\'', "MathJax_MathItalic");
                insertWrapper(operatorWrapper);
                return false;
            } else if (character === '^') {
                // superscript shortcut
                var superscriptWrapper = new eqEd.SuperscriptWrapper(equation);
                insertWrapper(superscriptWrapper);
                return false;
            } else if (character === '_') {
                // subscript shortcut
                var subscriptWrapper = new eqEd.SubscriptWrapper(equation);
                insertWrapper(subscriptWrapper);
                return false;
            } else if (character === '_') {
                // copy
                var subscriptWrapper = new eqEd.SubscriptWrapper(equation);
                insertWrapper(subscriptWrapper);
                return false;
            } else {
                return;
            }
        }
    });

    $(document).on('keydown', function(e) {
        var cursor = $('.cursor');
        var highlighted = $('.highlighted');
        var container = null;
        if ((typeof cursor !== 'undefined' && cursor !== null && cursor.length > 0) || (typeof highlighted !== 'undefined' && highlighted !== null && highlighted.length > 0)) {
            if (e.which === 8) {
                // backspace
                if (cursor.length > 0) {
                    container = cursor.parent().data('eqObject');
                    if (!(container.parent instanceof eqEd.EmptyContainerWrapper)) {
                        if (highlightStartIndex !== 0 && highlightStartIndex !== null) {
                            if (container.wrappers[highlightStartIndex - 1].childContainers.length > 0) {
                                container.wrappers[highlightStartIndex - 1].domObj.value.addClass('highlighted');
                                var endIndex = highlightStartIndex
                                highlightStartIndex = highlightStartIndex - 1;
                                updateHighlightFormatting(container, endIndex);
                                removeCursor();
                            } else {
                                highlightStartIndex = highlightStartIndex - 1;
                                container.removeWrappers(highlightStartIndex);
                                container.updateAll();
                                addCursorAtIndex(container, highlightStartIndex);
                            }
                        }
                    }
                } else if (highlighted.length > 0) {
                    container = highlighted.parent().data('eqObject');
                    if (!(container.parent instanceof eqEd.EmptyContainerWrapper)) {
                        var deleteWrappers;
                        if (highlightStartIndex < highlightEndIndex) {
                            deleteWrappers = _.range(highlightStartIndex, highlightEndIndex);
                        } else {
                            deleteWrappers = _.range(highlightEndIndex, highlightStartIndex);
                        }
                        eqEd.Container.prototype.removeWrappers.apply(container, deleteWrappers);
                        container.updateAll();
                        highlightStartIndex = (highlightStartIndex < highlightEndIndex) ? highlightStartIndex : highlightEndIndex;
                        updateHighlightFormatting(container, highlightStartIndex);
                        addCursorAtIndex(container, highlightStartIndex);
                    }
                }
                if (container !== null && container.wrappers.length === 0) {
                    if (container.parent instanceof eqEd.Equation) {
                        container.addWrappers([0, new eqEd.TopLevelEmptyContainerWrapper(container.equation)]);
                        container.updateAll();
                        addCursorAtIndex(container, 0);
                    } else {
                        container.addWrappers([0, new eqEd.SquareEmptyContainerWrapper(container.equation)]);
                        container.updateAll();
                        addCursorAtIndex(container.wrappers[0].childContainers[0], 0);
                    }
                    
                }
                return false;
            } else if (e.which === 46) {
                // delete
                if (cursor.length > 0) {
                    container = cursor.parent().data('eqObject');
                    if (!(container.parent instanceof eqEd.EmptyContainerWrapper)) {
                        if (highlightStartIndex !== container.wrappers.length && highlightStartIndex !== null) {
                            if (container.wrappers[highlightStartIndex].childContainers.length > 0) {
                                container.wrappers[highlightStartIndex].domObj.value.addClass('highlighted');
                                var endIndex = highlightStartIndex + 1;
                                updateHighlightFormatting(container, endIndex);
                                removeCursor();
                            } else {
                                container.removeWrappers(highlightStartIndex);
                                container.updateAll();
                                addCursorAtIndex(container, highlightStartIndex);
                            }
                            
                        }
                    }
                } else if (highlighted.length > 0) {
                    container = highlighted.parent().data('eqObject');
                    if (!(container.parent instanceof eqEd.EmptyContainerWrapper)) {
                        var deleteWrappers;
                        if (highlightStartIndex < highlightEndIndex) {
                            deleteWrappers = _.range(highlightStartIndex, highlightEndIndex);
                        } else {
                            deleteWrappers = _.range(highlightEndIndex, highlightStartIndex);
                        }
                        eqEd.Container.prototype.removeWrappers.apply(container, deleteWrappers);
                        container.updateAll();
                        highlightStartIndex = (highlightStartIndex < highlightEndIndex) ? highlightStartIndex : highlightEndIndex;
                        updateHighlightFormatting(container, highlightStartIndex);
                        addCursorAtIndex(container, highlightStartIndex);
                    }
                }
                if (container !== null && container.wrappers.length === 0) {
                    if (container.parent instanceof eqEd.Equation) {
                        container.addWrappers([0, new eqEd.TopLevelEmptyContainerWrapper(container.equation)]);
                        container.updateAll();
                        addCursorAtIndex(container, 0);
                    } else {
                        container.addWrappers([0, new eqEd.SquareEmptyContainerWrapper(container.equation)]);
                        container.updateAll();
                        addCursorAtIndex(container.wrappers[0].childContainers[0], 0);
                    }
                    
                }
                return false;
            } else if (e.which === 37) {
                // left
                if (cursor.length > 0) {
                    container = cursor.parent().data('eqObject');
                    if (container.wrappers[0] instanceof eqEd.TopLevelEmptyContainerWrapper) {
                        return false;
                    }
                    if (highlightStartIndex !== 0 && !(container instanceof eqEd.SquareEmptyContainer)) {
                        if (container.wrappers[highlightStartIndex - 1].childContainers.length > 0) {
                            if (container.wrappers[highlightStartIndex - 1].childContainers[container.wrappers[highlightStartIndex - 1].childContainers.length - 1].wrappers[0] instanceof eqEd.EmptyContainerWrapper) {
                                addCursorAtIndex(container.wrappers[highlightStartIndex - 1].childContainers[container.wrappers[highlightStartIndex - 1].childContainers.length - 1].wrappers[0].childContainers[0], 0);
                            } else {
                                // TODO: The following line is ridiculous...try to refactor to make easier to understand.
                                addCursorAtIndex(container.wrappers[highlightStartIndex - 1].childContainers[container.wrappers[highlightStartIndex - 1].childContainers.length - 1], container.wrappers[highlightStartIndex - 1].childContainers[container.wrappers[highlightStartIndex - 1].childContainers.length - 1].wrappers.length);
                            }
                        } else {
                            addCursorAtIndex(container, highlightStartIndex - 1);
                        }   
                    } else {
                        if (container instanceof eqEd.SquareEmptyContainer) {
                            container = container.parent.parent;
                        }
                        if (container.domObj.value.prev('.eqEdContainer').length > 0) {
                            container = container.domObj.value.prev('.eqEdContainer').first().data('eqObject');
                            if (container.wrappers[0] instanceof eqEd.SquareEmptyContainerWrapper) {
                                container = container.wrappers[0].childContainers[0];
                            }
                            addCursorAtIndex(container, container.wrappers.length);
                        } else {
                            if (!(container.parent instanceof eqEd.Equation)) {
                                addCursorAtIndex(container.parent.parent, container.parent.index);
                            }
                        }
                    }
                } else if (highlighted.length > 0) {
                    container = highlighted.parent().data('eqObject');
                    var cursorIndex = (highlightStartIndex < highlightEndIndex) ? highlightStartIndex : highlightEndIndex;
                    addCursorAtIndex(container, cursorIndex);
                    updateHighlightFormatting(container, cursorIndex);
                    $('.highlighted').removeClass('highlighted');
                }
                return false;
            } else if (e.which === 39) {
                // right
                if (cursor.length > 0) {
                    container = cursor.parent().data('eqObject');
                    if (container.wrappers[0] instanceof eqEd.TopLevelEmptyContainerWrapper) {
                        return false;
                    }
                    if (highlightStartIndex !== container.wrappers.length && !(container instanceof eqEd.SquareEmptyContainer)) {
                        if (container.wrappers[highlightStartIndex].childContainers.length > 0) {
                            if (container.wrappers[highlightStartIndex].childContainers[0].wrappers[0] instanceof eqEd.EmptyContainerWrapper) {
                                addCursorAtIndex(container.wrappers[highlightStartIndex].childContainers[0].wrappers[0].childContainers[0], 0);
                            } else {
                                addCursorAtIndex(container.wrappers[highlightStartIndex].childContainers[0], 0);
                            }
                        } else {
                            addCursorAtIndex(container, highlightStartIndex + 1);
                        }   
                    } else {
                        if (container instanceof eqEd.SquareEmptyContainer) {
                            container = container.parent.parent;
                        }
                        if (container.domObj.value.next('.eqEdContainer').length > 0) {
                            container = container.domObj.value.next('.eqEdContainer').first().data('eqObject');
                            if (container.wrappers[0] instanceof eqEd.SquareEmptyContainerWrapper) {
                                container = container.wrappers[0].childContainers[0];
                            }
                            addCursorAtIndex(container, 0);
                        } else {
                            if (!(container.parent instanceof eqEd.Equation)) {
                                addCursorAtIndex(container.parent.parent, container.parent.index + 1);
                            }
                        }
                    }
                } else if (highlighted.length > 0) {
                    container = highlighted.parent().data('eqObject');
                    var cursorIndex = (highlightStartIndex > highlightEndIndex) ? highlightStartIndex : highlightEndIndex;
                    addCursorAtIndex(container, cursorIndex);
                    updateHighlightFormatting(container, cursorIndex);
                    $('.highlighted').removeClass('highlighted');
                }
                return false;
            } else {
                return;
            }
        }
    });
};

/* End eq/js/keyboardInteraction.js*/

/* Begin eq/js/menuInteraction.js*/

var setupMenuEvents = function() {
    var getEquation = function() {
        var equation = null;
        if ($('.cursor').length > 0) {
            equation = $('.cursor').parent().data('eqObject').equation;
        } else if ($('.highlight').length > 0) {
            equation = $('.highlight').parent().data('eqObject').equation;
        }
        return equation;
    };

    $(document).on('touchstart mousedown', '#stackedFractionButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var stackedFractionWrapper = new eqEd.StackedFractionWrapper(equation);
        insertWrapper(stackedFractionWrapper);
    });
    $(document).on('touchstart mousedown', '#superscriptButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var superscriptWrapper = new eqEd.SuperscriptWrapper(equation);
        insertWrapper(superscriptWrapper);
    });
    $(document).on('touchstart mousedown', '#subscriptButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var subscriptWrapper = new eqEd.SubscriptWrapper(equation);
        insertWrapper(subscriptWrapper);
    });
    $(document).on('touchstart mousedown', '#superscriptAndSubscriptButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var superscriptAndSubscriptWrapper = new eqEd.SuperscriptAndSubscriptWrapper(equation);
        insertWrapper(superscriptAndSubscriptWrapper);
    });
    $(document).on('touchstart mousedown', '#squareRootButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var squareRootWrapper = new eqEd.SquareRootWrapper(equation);
        insertWrapper(squareRootWrapper);
    });
    $(document).on('touchstart mousedown', '#nthRootButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var nthRootButton = new eqEd.NthRootWrapper(equation);
        insertWrapper(nthRootButton);
    });
    
    $(document).on('touchstart mousedown', '#leftAngleBracketButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var leftAngleBracketWrapper = new eqEd.BracketWrapper(equation, "leftAngleBracket");
        insertWrapper(leftAngleBracketWrapper);
    });
    $(document).on('touchstart mousedown', '#rightAngleBracketButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var rightAngleBracketWrapper = new eqEd.BracketWrapper(equation, "rightAngleBracket");
        insertWrapper(rightAngleBracketWrapper);
    });
    $(document).on('touchstart mousedown', '#leftFloorBracketButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var leftFloorBracketWrapper = new eqEd.BracketWrapper(equation, "leftFloorBracket");
        insertWrapper(leftFloorBracketWrapper);
    });
    $(document).on('touchstart mousedown', '#rightFloorBracketButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var rightFloorBracketWrapper = new eqEd.BracketWrapper(equation, "rightFloorBracket");
        insertWrapper(rightFloorBracketWrapper);
    });
    $(document).on('touchstart mousedown', '#leftCeilBracketButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var leftCeilBracketWrapper = new eqEd.BracketWrapper(equation, "leftCeilBracket");
        insertWrapper(leftCeilBracketWrapper);
    });
    $(document).on('touchstart mousedown', '#rightCeilBracketButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var rightCeilBracketWrapper = new eqEd.BracketWrapper(equation, "rightCeilBracket");
        insertWrapper(rightCeilBracketWrapper);
    });
    $(document).on('touchstart mousedown', '#parenthesesBracketPairButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var parenthesesBracketPair = new eqEd.BracketPairWrapper(equation, "parenthesisBracket");
        insertWrapper(parenthesesBracketPair);
    });
    $(document).on('touchstart mousedown', '#squareBracketPairButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var squareBracketPair = new eqEd.BracketPairWrapper(equation, "squareBracket");
        insertWrapper(squareBracketPair);
    });
    $(document).on('touchstart mousedown', '#curlyBracketPairButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var curlyBracketPair = new eqEd.BracketPairWrapper(equation, "curlyBracket");
        insertWrapper(curlyBracketPair);
    });
    $(document).on('touchstart mousedown', '#angleBracketPairButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var angleBracketPair = new eqEd.BracketPairWrapper(equation, "angleBracket");
        insertWrapper(angleBracketPair);
    });$(document).on('touchstart mousedown', '#floorBracketPairButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var floorBracketPair = new eqEd.BracketPairWrapper(equation, "floorBracket");
        insertWrapper(floorBracketPair);
    });
    $(document).on('touchstart mousedown', '#ceilBracketPairButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var ceilBracketPair = new eqEd.BracketPairWrapper(equation, "ceilBracket");
        insertWrapper(ceilBracketPair);
    });
    $(document).on('touchstart mousedown', '#absValBracketPairButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var absValBracketPair = new eqEd.BracketPairWrapper(equation, "absValBracket");
        insertWrapper(absValBracketPair);
    });
    $(document).on('touchstart mousedown', '#normBracketPairButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var normBracketPair = new eqEd.BracketPairWrapper(equation, "normBracket");
        insertWrapper(normBracketPair);
    });
    $(document).on('touchstart mousedown', '#sumBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, true, true, 'sum');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#sumBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, true, 'sum');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#sumBigOperatorNoUpperNoLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, false, 'sum');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigCapBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, true, true, 'bigCap');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigCapBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, true, 'bigCap');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigCapBigOperatorNoUpperNoLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, false, 'bigCap');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigCupBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, true, true, 'bigCup');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigCupBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, true, 'bigCup');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigCupBigOperatorNoUpperNoLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, false, 'bigCup');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigSqCapBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, true, true, 'bigSqCap');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigSqCapBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, true, 'bigSqCap');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigSqCapBigOperatorNoUpperNoLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, false, 'bigSqCap');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigSqCupBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, true, true, 'bigSqCup');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigSqCupBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, true, 'bigSqCup');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigSqCupBigOperatorNoUpperNoLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, false, 'bigSqCup');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#prodBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, true, true, 'prod');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#prodBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, true, 'prod');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#prodBigOperatorNoUpperNoLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, false, 'prod');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#coProdBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, true, true, 'coProd');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#coProdBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, true, 'coProd');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#coProdBigOperatorNoUpperNoLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, false, 'coProd');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigVeeBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, true, true, 'bigVee');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigVeeBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, true, 'bigVee');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigVeeBigOperatorNoUpperNoLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, false, 'bigVee');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigWedgeBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, true, true, 'bigWedge');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigWedgeBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, true, 'bigWedge');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#bigWedgeBigOperatorNoUpperNoLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, false, false, false, 'bigWedge');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineSumBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, true, true, 'sum');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineSumBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, false, true, 'sum');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineBigCapBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, true, true, 'bigCap');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineBigCapBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, false, true, 'bigCap');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineBigCupBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, true, true, 'bigCup');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineBigCupBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, false, true, 'bigCup');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineBigSqCapBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, true, true, 'bigSqCap');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineBigSqCapBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, false, true, 'bigSqCap');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineBigSqCupBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, true, true, 'bigSqCup');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineBigSqCupBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, false, true, 'bigSqCup');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineProdBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, true, true, 'prod');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineProdBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, false, true, 'prod');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineCoProdBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, true, true, 'coProd');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineCoProdBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, false, true, 'coProd');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineBigVeeBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, true, true, 'bigVee');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineBigVeeBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, false, true, 'bigVee');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineBigWedgeBigOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, true, true, 'bigWedge');
        insertWrapper(bigOperator);
    });
    $(document).on('touchstart mousedown', '#inlineBigWedgeBigOperatorNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var bigOperator = new eqEd.BigOperatorWrapper(equation, true, false, true, 'bigWedge');
        insertWrapper(bigOperator);
    });

    ////////////////////////////////////////////////////////////////

    $(document).on('touchstart mousedown', '#integralButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, true, true, 'single');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#integralNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, false, true, 'single');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#integralNoUpperNoLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, false, false, 'single');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#doubleIntegralButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, true, true, 'double');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#doubleIntegralNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, false, true, 'double');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#doubleIntegralNoUpperNoLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, false, false, 'double');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#tripleIntegralButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, true, true, 'triple');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#tripleIntegralNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, false, true, 'triple');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#tripleIntegralNoUpperNoLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, false, false, 'triple');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#contourIntegralButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, true, true, 'singleContour');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#contourIntegralNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, false, true, 'singleContour');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#contourIntegralNoUpperNoLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, false, false, 'singleContour');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#contourDoubleIntegralButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, true, true, 'doubleContour');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#contourDoubleIntegralNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, false, true, 'doubleContour');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#contourDoubleIntegralNoUpperNoLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, false, false, 'doubleContour');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#contourTripleIntegralButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, true, true, 'tripleContour');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#contourTripleIntegralNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, false, true, 'tripleContour');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#contourTripleIntegralNoUpperNoLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, false, false, false, 'tripleContour');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#inlineIntegralButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, true, true, true, 'single');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#inlineIntegralNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, true, false, true, 'single');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#inlineDoubleIntegralButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, true, true, true, 'double');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#inlineDoubleIntegralNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, true, false, true, 'double');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#inlineTripleIntegralButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, true, true, true, 'triple');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#inlineTripleIntegralNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, true, false, true, 'triple');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#inlineContourIntegralButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, true, true, true, 'singleContour');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#inlineContourIntegralNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, true, false, true, 'singleContour');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#inlineContourDoubleIntegralButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, true, true, true, 'doubleContour');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#inlineContourDoubleIntegralNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, true, false, true, 'doubleContour');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#inlineContourTripleIntegralButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, true, true, true, 'tripleContour');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#inlineContourTripleIntegralNoUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var integralWrapper = new eqEd.IntegralWrapper(equation, true, false, true, 'tripleContour');
        insertWrapper(integralWrapper);
    });

    $(document).on('touchstart mousedown', '#partialDifferentialButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var differentialWrapper = new eqEd.SymbolWrapper(equation, '∂', "MathJax_Main");
        insertWrapper(differentialWrapper);
    });

    ///////////////////////////////////////////////////////

    $(document).on('touchstart mousedown', '#logButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'log', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#lnButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'ln', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#limButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'lim', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#maxButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'max', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#minButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'min', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#supButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'sup', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#infButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'inf', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#sinButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'sin', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#cosButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'cos', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#tanButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'tan', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#cotButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'cot', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#secButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'sec', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#cscButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'csc', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#sinhButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'sinh', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#coshButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'cosh', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#tanhButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'tanh', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#cothButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'coth', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#sechButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'sech', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#cschButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionWrapper(equation, 'csch', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#limitButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var limitWrapper = new eqEd.LimitWrapper(equation);
        insertWrapper(limitWrapper);
    });

    $(document).on('touchstart mousedown', '#maxLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionLowerWrapper(equation, 'max', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#minLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.FunctionLowerWrapper(equation, 'min', "MathJax_Main");
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#logLowerButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var functionWrapper = new eqEd.LogLowerWrapper(equation);
        insertWrapper(functionWrapper);
    });

    $(document).on('touchstart mousedown', '#matrixButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        $('#rows').blur();
        $('#cols').blur();
        var rows = parseInt($('#rows').val());
        var cols = parseInt($('#cols').val());
        var matrixWrapper = new eqEd.MatrixWrapper(equation, rows, cols, 'center');
        insertWrapper(matrixWrapper);
    });

    $(document).on('touchstart mousedown', '#dotAccentButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var accentWrapper = new eqEd.AccentWrapper(equation, '˙', 'MathJax_Main');
        insertWrapper(accentWrapper);
    });

    $(document).on('touchstart mousedown', '#hatAccentButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var accentWrapper = new eqEd.AccentWrapper(equation, '^', 'MathJax_Main');
        insertWrapper(accentWrapper);
    });

    $(document).on('touchstart mousedown', '#vectorAccentButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var accentWrapper = new eqEd.AccentWrapper(equation, '⃗', 'MathJax_Main');
        insertWrapper(accentWrapper);
    });

    $(document).on('touchstart mousedown', '#barAccentButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var accentWrapper = new eqEd.AccentWrapper(equation, '¯', 'MathJax_Main');
        insertWrapper(accentWrapper);
    });

    $(document).on('touchstart mousedown', '#gammaUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'Γ', "MathJax_Main");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#deltaUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'Δ', "MathJax_Main");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#thetaUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'Θ', "MathJax_Main");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#lambdaUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'Λ', "MathJax_Main");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#xiUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'Ξ', "MathJax_Main");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#piUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'Π', "MathJax_Main");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#sigmaUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'Σ', "MathJax_Main");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#upsilonUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'Υ', "MathJax_Main");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#phiUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'Φ', "MathJax_Main");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#psiUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'Ψ', "MathJax_Main");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#omegaUpperButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'Ω', "MathJax_Main");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#alphaButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'α', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#betaButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'β', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#gammaButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'γ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#deltaButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'δ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#varEpsilonButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'ε', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#epsilonButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'ϵ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#zetaButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'ζ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#etaButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'η', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#thetaButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'θ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#varThetaButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'ϑ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#iotaButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'ι', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#kappaButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'κ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#lambdaButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'λ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#muButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'μ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#nuButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'ν', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#xiButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'ξ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#piButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'π', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#varPiButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'ϖ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#rhoButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'ρ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#varRhoButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'ϱ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#sigmaButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'σ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#varSigmaButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'ς', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#tauButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'τ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#upsilonButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'υ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#varPhiButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'φ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#phiButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'ϕ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#chiButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'χ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#psiButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'ψ', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#omegaButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, 'ω', "MathJax_MathItalic");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#lessThanOrEqualToButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '≤', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });
    $(document).on('touchstart mousedown', '#greaterThanOrEqualToButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '≥', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });
    $(document).on('touchstart mousedown', '#circleOperatorButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '◦', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });
    $(document).on('touchstart mousedown', '#approxEqualToButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '≈', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });
    $(document).on('touchstart mousedown', '#belongsToButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '∈', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#timesButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '×', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#pmButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '±', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#wedgeButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '∧', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#veeButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '∨', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#equivButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '≡', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#congButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '≅', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#neqButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '≠', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#simButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '∼', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#proptoButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '∝', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#precButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '≺', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#precEqButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '⪯', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#subsetButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '⊂', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#subsetEqButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '⊆', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#succButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '≻', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#succEqButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '⪰', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#perpButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '⊥', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#midButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '∣', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#parallelButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, '∥', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });
    $(document).on('touchstart mousedown', '#comaButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, ',', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#colonButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var operatorWrapper = new eqEd.OperatorWrapper(equation, ':', "MathJax_Main");
        insertWrapper(operatorWrapper);
    });

    $(document).on('touchstart mousedown', '#partialButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, '∂', "MathJax_Main");
        insertWrapper(symbolWrapper);
    });

    $(document).on('touchstart mousedown', '#infinityButton', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var equation = getEquation();
        var symbolWrapper = new eqEd.SymbolWrapper(equation, '∞', "MathJax_Main");
        insertWrapper(symbolWrapper);
    });
};
$(document).on('touchstart mousedown', '.eq-tabs .eq-tab-links a', function(e)  {
	e.stopPropagation();
	e.preventDefault();
	var currentAttrValue = $(this).attr('href');
    // Show/Hide Tabs
    $('.eq-tabs ' + currentAttrValue).show().siblings().hide();
    // Change/remove current tab to active
    $(this).parent('li').addClass('active').siblings().removeClass('active');
});

/* End eq/js/menuInteraction.js*/

/* Begin eq/js/equationEditor.js*/

var fontsLoaded = false;
var imagesLoaded = false;
var spinner = null;
$(document).ready(function() {
    var opts = {
      lines: 9 // The number of lines to draw
    , length: 12 // The length of each line
    , width: 6 // The line thickness
    , radius: 12 // The radius of the inner circle
    , scale: 1 // Scales overall size of the spinner
    , corners: 1 // Corner roundness (0..1)
    , color: '#000' // #rgb or #rrggbb or array of colors
    , opacity: 0.25 // Opacity of the lines
    , rotate: 0 // The rotation offset
    , direction: 1 // 1: clockwise, -1: counterclockwise
    , speed: 1 // Rounds per second
    , trail: 60 // Afterglow percentage
    , fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
    , zIndex: 2e9 // The z-index (defaults to 2000000000)
    , className: 'spinner' // The CSS class to assign to the spinner
    , top: '50%' // Top position relative to parent
    , left: '50%' // Left position relative to parent
    , shadow: false // Whether to render a shadow
    , hwaccel: false // Whether to use hardware acceleration
    , position: 'absolute' // Element positioning
    };

    var target = document.getElementById('loadingMessage');
    spinner = new Spinner(opts).spin(target);
});

var setup = function() {
    if (fontsLoaded && imagesLoaded) {
        spinner.stop();
        $('#loadingMessage').remove();
        $('#loadingMessageOuter').remove();
        initializePropertyHooks();
        setupKeyboardEvents();
        setupMenuEvents();
        var equation = new eqEd.Equation();
        $('.equation-editor').replaceWith(equation.domObj.value);
        equation.updateAll();
        //setupInitialContainer();
    }
}

// preload fonts, using webfont.js
var loadFonts = function(callback) {
    WebFont.load({
        custom: {
            families: ['MathJax_Main:n4,i4', 'MathJax_Math:i4', 'MathJax_Size1:n4', 'MathJax_Size2:n4', 'MathJax_Size3:n4', 'MathJax_Size4:n4', 'MathJax_AMS:n4'],
            testStrings: {
                'MathJax_Size2:n4': '\u2211\u22C2\u2A00\u220F\u22C3\u2A02\u2210\u2A06\u2A01\u222B\u22C1\u2A04'
            },
            urls: ['eq/Fonts/TeX/font.css']
        },
        active: function() {
            fontsLoaded = true;
            callback();
        },
        inactive: function() {
            console.log("Failed to load fonts.");
        }
    });
};

// preload images
// arrayOfImages is an array of the paths to images you want to preload
// ex) ['../../Images/radical.png', '../../Images/radicalHighlight.png', '../../Images/radicalDiagonalLine.png']
var loadImages = function(arrayOfImages, callback) {
    $(arrayOfImages).each(function () {
        $('<img />').attr('src',this).appendTo('body').css('display','none');
    });
    imagesLoaded = true;
    callback();
};

loadFonts(setup);
loadImages([], setup);

/* End eq/js/equationEditor.js*/

/* Begin eq/js/latexGenerator.js*/

var generateLatex = function(expr) {
	var latexString = '';
	if(typeof expr == 'object')
	{
		for (var i = 0; i < expr.length; i++) {
			var wrapper = expr[i];
			switch (wrapper.type) {
				case "Symbol":
					latexString += symbolToLatex(wrapper);
					break;
				case "BigOperator":
					latexString += bigOperatorToLatex(wrapper);
					break;
				case "Function":
					latexString += functionToLatex(wrapper);
					break;
				case "Bracket":
					latexString += bracketToLatex(wrapper);
					break;
				case "Operator":
					latexString += operatorToLatex(wrapper);
					break;
				case "BracketPair":
					latexString += bracketPairToLatex(wrapper);
					break;
				case "Integral":
					latexString += integralToLatex(wrapper);
					break;
				case "Accent":
					latexString += accentToLatex(wrapper);
					break;
				case "FunctionLower":
					latexString += functionLowerToLatex(wrapper);
					break;
				case "Limit":
					latexString += limitToLatex(wrapper);
					break;
				case "LogLower":
					latexString += logLowerToLatex(wrapper);
					break;
				case "Matrix":
					latexString += matrixToLatex(wrapper);
					break;
				case "NthRoot":
					latexString += nthRootToLatex(wrapper);
					break;
				case "SquareRoot":
					latexString += squareRootToLatex(wrapper);
					break;
				case "StackedFraction":
					latexString += stackedFractionToLatex(wrapper);
					break;
				case "Superscript":
					latexString += superscriptToLatex(wrapper);
					var endBraces = '';
					while (typeof expr[i + 1] !== "undefined" && expr[i + 1].type === "Superscript") {
						i++;
						latexString = latexString.substring(0, latexString.length - 1);
						endBraces += '}';
						latexString += superscriptToLatex(expr[i]);
					}
					latexString += endBraces;
					break;
				case "Subscript":
					latexString += subscriptToLatex(wrapper);
					var endBraces = '';
					while (typeof expr[i + 1] !== "undefined" && expr[i + 1].type === "Subscript") {
						i++;
						latexString = latexString.substring(0, latexString.length - 1);
						endBraces += '}';
						latexString += subscriptToLatex(expr[i]);
					}
					latexString += endBraces;
					break;
				case "SuperscriptAndSubscript":
					var superscripts = [];
					var subscripts = [];
					superscripts.push(wrapper.operands.superscript);
					subscripts.push(wrapper.operands.subscript);
					while (typeof expr[i + 1] !== "undefined" && expr[i + 1].type === "SuperscriptAndSubscript") 
					{
						i++;
						superscripts.push(expr[i].operands.superscript);
						subscripts.push(expr[i].operands.subscript);
					}
					while (typeof expr[i + 1] !== "undefined" && expr[i + 1].type === "Superscript") 
					{
						i++;
						superscripts.push(expr[i].operands.superscript);
					}
					while (typeof expr[i + 1] !== "undefined" && expr[i + 1].type === "Subscript") 
					{
						i++;
						subscripts.push(expr[i].operands.subscript);
					}
					var supString = '';
					var supEndBraces = '';
					for (var j = 0; j < superscripts.length; j++) 
					{
						supString += '^{' + generateLatex(superscripts[j]);
						supEndBraces += '}';
					}
					supString += supEndBraces;
					var subString = '';
					var subEndBraces = '';
					for (var j = 0; j < subscripts.length; j++) 
					{
						subString += '_{' + generateLatex(subscripts[j]);
						subEndBraces += '}';
					}
					subString += subEndBraces;
					latexString += subString + supString;
					break;
			}
		}
	}
	return latexString;
}

var symbolToLatex = function(expr) {
	var latexString = '';
	var symbolToLatexMapping = {
		'∂': '\\partial ',
		'∞': '\\infty ',
		'Γ': '\\Gamma ',
		'Δ': '\\Delta ',
		'Θ': '\\Theta ',
		'Λ': '\\Lambda ',
		'Ξ': '\\Xi ',
		'Π': '\\Pi ',
		'Σ': '\\Sigma ',
		'Υ': '\\Upsilon ',
		'Φ': '\\Phi ',
		'Ψ': '\\Psi ',
		'Ω': '\\Omega ',
		'α': '\\alpha ',
		'β': '\\beta ',
		'γ': '\\gamma ',
		'δ': '\\delta ',
		'ε': '\\varepsilon ',
		'ϵ': '\\epsilon ',
		'ζ': '\\zeta ',
		'η': '\\eta ',
		'θ': '\\theta ',
		'ϑ': '\\vartheta ',
		'ι': '\\iota ',
		'κ': '\\kappa ',
		'λ': '\\lambda ',
		'μ': '\\mu ',
		'ν': '\\nu ',
		'ξ': '\\xi ',
		'π': '\\pi ',
		'ϖ': '\\varpi ',
		'ρ': '\\rho ',
		'ϱ': '\\varrho ',
		'σ': '\\sigma ',
		'ς': '\\varsigma ',
		'τ': '\\tau ',
		'υ': '\\upsilon ',
		'φ': '\\varphi ',
		'ϕ': '\\phi ',
		'χ': '\\chi ',
		'ψ': '\\psi ',
		'ω': '\\omega ',
		'ı': '\\imath ',
		'ȷ': '\\jmath '
	}
	if (typeof symbolToLatexMapping[expr.value] === 'undefined') {
		latexString = expr.value;
	} else {
		latexString = symbolToLatexMapping[expr.value];
	}
	return latexString;
}

var bigOperatorToLatex = function(expr) {
	var latexString = '';
	var lowerLimitString = '';
	var upperLimitString = '';
	var operandString = '';
	if(typeof expr.operands != "undefined" && expr.operands !== null)
	{
		if (typeof expr.operands.lowerLimit !== "undefined") {
			lowerLimitString = '_{' + generateLatex(expr.operands.lowerLimit) + '}';
		}
		if (typeof expr.operands.upperLimit !== "undefined") {
			upperLimitString = '^{' + generateLatex(expr.operands.upperLimit) + '}';
		}
		operandString = generateLatex(expr.operands.operands);
	}
	var bigOperatorToLatexMapping = {
        sum: '\\sum ',
        bigCap: '\\bigcap ',
        bigCup: '\\bigcup ',
        bigSqCap: '\\sqcap ',
        bigSqCup: '\\bigsqcup ',
        prod: '\\prod ',
        coProd: '\\coprod ',
        bigVee: '\\bigvee ',
        bigWedge: '\\bigwedge '
	}
	latexString = bigOperatorToLatexMapping[expr.value] + lowerLimitString + upperLimitString + operandString;
	return latexString;
}

var functionToLatex = function(expr) {
	var latexString = '';
	latexString = '\\' + expr.value;
	return latexString;
}

var bracketToLatex = function(expr) {
	var latexString = '';
	var bracketToLatexMapping = {
        leftParenthesisBracket: '\\left(',
        rightParenthesisBracket: '\\right)',
        leftSquareBracket: '\\left[',
        rightSquareBracket: '\\right]',
        leftCurlyBracket: '\\left\\{',
        rightCurlyBracket: '\\right\\}',
        leftAngleBracket: '\\left\\langle',
        rightAngleBracket: '\\right\\rangle',
        leftFloorBracket: '\\left\\lfloor',
        rightFloorBracket: '\\right\\rfloor',
        leftCeilBracket: '\\left\\lceil',
        rightCeilBracket: '\\right\\rceil'
    };
    latexString = bracketToLatexMapping[expr.value];
	return latexString;
}

var operatorToLatex = function(expr) {
	var latexString = '';
	var operatorToLatexMapping = {
		'+': '+',
		'−': '-',
		'=': '=',
		'<': '<',
		'>': '>',
		'≤': '\\leq ',
		'≥': '\\geq ',
		'≈': '\\approx ',
		'≡': '\\equiv ',
		'≅': '\\cong ',
		'≠': '\\neq ',
		'∼': '\\sim ',
		'∝': '\\propto ',
		'≺': '\\prec ',
		'⪯': '\\preceq ',
		'⊂': '\\subset ',
		'⊆': '\\subseteq ',
		'≻': '\\succ ',
		'⪰': '\\succeq ',
		'◦': '\\circ ',
		'∈': '\\in ',
		'×': '\\times ',
		'±': '\\pm ',
		'∧': '\\wedge ',
		'∨': '\\vee ',
		'⊥': '\\perp ',
		'∣': '\\mid ',
		'∥': '\\parallel ',
		':': ':',
		'÷': '\\div ',
		'⋅': '\\cdot ',
		'=': '=',
		',': ','
	};
	latexString = operatorToLatexMapping[expr.value];
	return latexString;
}

var bracketPairToLatex = function(expr) {
	var latexString = '';
	var bracketedExpression = generateLatex(expr.operands.bracketedExpression);
	var bracketPairToLatexMapping = {
        "parenthesisBracket": '\\left(' + bracketedExpression + '\\right)',
        "squareBracket": '\\left[' + bracketedExpression + '\\right]',
        "curlyBracket": '\\left\\{' + bracketedExpression + '\\right\\}',
        "angleBracket": '\\left\\langle' + bracketedExpression + '\\right\\rangle',
        "floorBracket": '\\left\\lfloor' + bracketedExpression + '\\right\\rfloor',
        "ceilBracket": '\\left\\lceil' + bracketedExpression + '\\right\\rceil',
        "absValBracket": '\\left|' + bracketedExpression + '\\right|',
        "normBracket": '\\left\\|' + bracketedExpression + '\\right\\|'
    };
    latexString = bracketPairToLatexMapping[expr.value];
	return latexString;
}

var integralToLatex = function(expr) {
	var latexString = '';
	var lowerLimitString = '';
	var upperLimitString = '';
	if(typeof expr.operands != "undefined" && expr.operands !== null)
	{
		if (typeof expr.operands.lowerLimit != "undefined") {
			lowerLimitString = '_{' + generateLatex(expr.operands.lowerLimit) + '}';
		}
		if (typeof expr.operands.upperLimit != "undefined") {
			upperLimitString = '^{' + generateLatex(expr.operands.upperLimit) + '}';
		}
	}
	var integralToLatexMapping = {
        'single': '\\int ',
        'double': '\\iint ',
        'triple': '\\iiint ',
        'singleContour': '\\oint ',
        'doubleContour': '\\oiint ',
        'tripleContour': '\\oiiint '
	};
	latexString = integralToLatexMapping[expr.value] + lowerLimitString + upperLimitString;
	return latexString;
}

var accentToLatex = function(expr) {
	var latexString = '';
	var accentedExpression = '{' + generateLatex(expr.operands.accentedExpression) + '}';
	var accentToLatexMapping = {
		'˙': '\\dot ',
		'^': '\\hat ',
		'⃗': '\\vec ',
		'¯': '\\bar '
	}
	latexString = accentToLatexMapping[expr.value] + accentedExpression;
	return latexString;
}

var functionLowerToLatex = function(expr) {
	var latexString = '';
	var lower = '_{' + generateLatex(expr.operands.lower) + '}';
	latexString = '\\' + expr.value + lower;
	return latexString;
}

var limitToLatex = function(expr) {
	var latexString = '\\lim ';
	var lower = '_{' + generateLatex(expr.operands.left) + ' \\to ' + generateLatex(expr.operands.right) + '}';
	latexString += lower;
	return latexString;
}

var logLowerToLatex = function(expr) {
	var latexString = '\\log ';
	var lower = '_{' + generateLatex(expr.operands.lower) + '}';
	latexString += lower;
	return latexString;
}

var matrixToLatex = function(expr) {
	var latexString = '\\begin{array}{ccc}';
	for (var j = 0; j < expr.operands.elements.length; j++) {
		var row = expr.operands.elements[j];
		var rowString = '';
		for (var k = 0; k < row.length; k++) {
			rowString += generateLatex(row[k]) + ' & ';
		}
		rowString = rowString.substring(0, rowString.length - 2) + '\\\\\r\n';
		latexString += rowString;
	}
	latexString += '\\end{array}';
	return latexString;
}

var nthRootToLatex = function(expr) {
	var latexString = '\\sqrt';
	var degree = '[' + generateLatex(expr.operands.degree) + ']';
	var radicand = '{' + generateLatex(expr.operands.radicand) + '}';
	latexString += degree + radicand;
	return latexString;
}

var squareRootToLatex = function(expr) {
	var latexString = '\\sqrt';
	var radicand = '{' + generateLatex(expr.operands.radicand) + '}';
	latexString += radicand;
	return latexString;
}

var stackedFractionToLatex = function(expr) {
	var latexString = '\\frac';
	var numerator = '{' + generateLatex(expr.operands.numerator) + '}';
	var denominator = '{' + generateLatex(expr.operands.denominator) + '}';
	latexString += numerator + denominator;
	return latexString;
}

var subscriptToLatex = function(expr) {
	var latexString = '';
	var subscript = '_{' + generateLatex(expr.operands.subscript) + '}';
	latexString += subscript;
	return latexString;
}

var superscriptToLatex = function(expr) {
	var latexString = '';
	var superscript = '^{' + generateLatex(expr.operands.superscript) + '}';
	latexString += superscript;
	return latexString;
}

/* End eq/js/latexGenerator.js*/

