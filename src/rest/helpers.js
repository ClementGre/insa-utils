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

function isValidRep(date, wDay, mDay, allowWeekdays, type){ // type: 1: dej, 2: rep1, 3: rep2
    if(mDay >= date.getDate()){
        if(type === 0){
            return (wDay < 5 && (mDay !== date.getDate() || date.getHours() < 8));
        }
        if(type === 1){
            return (allowWeekdays || wDay < 5) && (mDay !== date.getDate() || date.getHours() < 14);
        }
        if(type === 2){
            return wDay !== 5 && (allowWeekdays || wDay < 5) && (mDay !== date.getDate() || date.getHours() < 20);
        }
    }
    return false;
}

export {debounce, isValidRep}