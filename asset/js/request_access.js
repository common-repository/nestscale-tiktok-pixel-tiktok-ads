(function($) {
    $(document).ready(function() {
        $('#btn-connect').click(function(e) {
            e.preventDefault();
            $(this).prop('disabled', true);
            // let access = $(this).data("access");
            // console.log(vote);

            $.ajax({
                type: "post",
                dataType: "json",
                url: admin_url,
                data: {
                    action: "wpns_tt_woo_allow_nestads",
                    // access: access,
                    nonce: nonce
                },
                context: this,
                beforeSend: function() {},
                success: function(response) {
                    if (response.success) {
                        if(response.data.status=='success'){
                            
                            window.location.href = response.data.redirect_url;
                        }else {
                            // window.location.href = index_url;
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('The following error occured: ' + textStatus, errorThrown);
                }
            })
            return false;


        });
        $('#go-to-app').click(function(e){
            e.preventDefault();
            $(this).prop('disabled', true);
            $.ajax({
                type: "post",
                dataType: "json",
                url: admin_url,
                data: {
                    action: "wpns_tt_woo_go_to_app",
                    // access: access,
                    nonce: nonce
                },
                context: this,
                beforeSend: function() {},
                success: function(response) {
                    if (response.success) {
                        if(response.data.status=='success'){
                            
                            window.location.href = response.data.redirect_url;
                        }else {
                            // window.location.href = index_url;
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('The following error occured: ' + textStatus, errorThrown);
                }
            })
            return false;
        });
    })
})(jQuery)