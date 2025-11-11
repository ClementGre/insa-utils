function remove_after(s, el, no_el) {
    if (no_el === undefined) no_el = true;
    var n = s.indexOf(el);
    s = s.substring(0, (no_el && n !== -1) ? n : s.length);
    return s;
}

function remove_before(s, el, no_el) {
    if (no_el === undefined) no_el = true;
    var n = s.indexOf(el);
    s = s.substring((no_el && n !== -1) ? n + 1 : 0);
    return s;
}

// For CommonJS compatibility if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        remove_after: remove_after,
        remove_before: remove_before
    };
}
