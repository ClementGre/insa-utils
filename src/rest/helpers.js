function debounce(fn, delay, alwaysExecuted = () => {}){
    let timeoutID = null
    
    return function(){
        
        clearTimeout(timeoutID)
        let args = arguments
        let that = this
        
        alwaysExecuted.apply(that, args)
        
        timeoutID = setTimeout(function(){
            fn.apply(that, args)
        }, delay)
    }
}
function isValidNotPassedRep(date, wDay, mDay, allowWeekends, type){ // type: 1: dej, 2: rep1, 3: rep2
    return isValidRep(date, wDay, allowWeekends, type) && !isPassedRep(date, mDay, allowWeekends, type);
}
function isValidPassedRep(date, wDay, mDay, allowWeekends, type){ // type: 1: dej, 2: rep1, 3: rep2
    return isValidRep(date, wDay, allowWeekends, type) && isPassedRep(date, mDay, allowWeekends, type);
}
function isValidRep(date, wDay, allowWeekends, type){ // type: 1: dej, 2: rep1, 3: rep2
    if(type === 0){
        return wDay < 5;
    }
    if(type === 1){
        return allowWeekends || wDay < 5;
    }
    if(type === 2){
        return wDay !== 5 && (allowWeekends || wDay < 5);
    }
    return false;
}
function isPassedRep(date, mDay, allowWeekends, type){ // type: 1: dej, 2: rep1, 3: rep2
    if(type === 0){
        return mDay < date.getDate() || (mDay === date.getDate() && date.getHours() < 8);
    }
    if(type === 1){
        return mDay < date.getDate() || (mDay === date.getDate() && date.getHours() < 14);
    }
    if(type === 2){
        return mDay < date.getDate() || (mDay === date.getDate() && date.getHours() < 20);
    }
    return false;
}

export {debounce, isValidRep, isPassedRep, isValidNotPassedRep, isValidPassedRep};
