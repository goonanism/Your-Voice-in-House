$(document).ready(function(){
	// accordion bit
	$('.primary_member_details').toggle(
		function(){
			$(this).next('.seconday_member_details').slideDown();
		},
		function(){
			$(this).next('.seconday_member_details').slideUp();
		}
	);
	$('.seconday_member_details').css('display', 'none');
	$('.primary_member_details').css('cursor', 'pointer');
	
	// form validation
	$('#MemberEmailForm').validate();
	$('.terms').click(function(){
		$('.dialog').dialog({
			modal: true,
			title: 'Terms & Conditions',
			width: 800,
			height: 600
		});
		$.post('/yvih2/members/terms', function(data){
			$('.dialog').html(data);
		});
		return false;
	});
	
	// ajax search
//	$('#MemberMember').autocomplete({		
//		source: function( request, response ) {
//			$.post();
//			};,
//		minlength: 2
//	});
}); // end ready

 jQuery.validator.addMethod("multiemail", function(value, element) {
                    if (this.optional(element)) {
                        return true;
                    }
                    var emails = value.split( new RegExp( "\\s*,\\s*", "gi" ) );
                    valid = true;
                    for(var i in emails) {
                        value = emails[i];
                        valid=valid && jQuery.validator.methods.email.call(this, value,element);
                    }
                    return valid;}, "Invalid email format");
