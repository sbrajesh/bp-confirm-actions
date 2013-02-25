jQuery(document).ready(function($){
  
    $(document).on('click','a.bp-needs-confirmation,a.leave-group',function(evt){
        
        if(confirm(BPConfirmaActions.confirm_message)){
            return true;
            
        }
            
        evt.stopImmediatePropagation();
        return false;
    })
    
    
    
    
});