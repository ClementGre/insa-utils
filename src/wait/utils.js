export function remove_after(s, el, no_el = true){
    const n = s.indexOf(el);
    s = s.substring(0, (no_el && n !== -1) ? n : s.length);
    return s;
}
export function remove_before(s, el, no_el = true){
    const n = s.indexOf(el);
    s = s.substring((no_el && n !== -1) ? n+1 : 0);
    return s;
}
