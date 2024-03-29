(function(){
	var e,t,n,
		r=function(e,t){
			return function(){
				return e.apply(t,arguments)
			}
		},
		i=[].slice;
	e=this.jQuery||this.Zepto;
	
	if(!e)throw "jQuery/Zepto required";

	this.PaymentTag=function(){
	
		function t(t){
			var n,i;
			t==null&&(t={}),
			this.changeCardType=r(this.changeCardType,this),
			this.restrictNumeric=r(this.restrictNumeric,this),
			this.formatNumber=r(this.formatNumber,this),
			this.handleToken=r(this.handleToken,this),
			this.submit=r(this.submit,this),
			this.$el=t.el||"<payment />",
			this.$el=e(this.$el),t.key||(t.key=this.$el.attr("key")||this.$el.attr("data-key")),
			(n=t.cvc)==null&&(t.cvc=this.$el.attr("nocvc")==null&&this.$el.attr("data-nocvc")==null),
			(i=t.token)==null&&(t.token=this.$el.attr("notoken")==null&&this.$el.attr("data-notoken")==null),
			t.form||(t.form=this.$el.parents("form")),
			this.options=e.extend({},this.defaults,t),this.options.key&&this.setKey(this.options.key),
			this.setForm(this.options.form),
			this.$el.delegate(".number input","keydown",this.formatNumber),
			this.$el.delegate(".number input","keyup",this.changeCardType),
			this.$el.delegate("input[type=tel]","keypress",this.restrictNumeric)
		}
		return t.replaceTags = function(t){
			var n=this;
			return t==null&&(t=document.body),
				e("payment, .payment-tag",t).each( function(e,t){
					return(new n({el:t})).render()
				}
			)
		},
		t.prototype.defaults={tokenName:"stripe_token",token:!0,cvc:!0},
		t.prototype.render=function(){
			return this.$el.html(this.constructor.view(this)),
				this.$number=this.$(".number input"),
				this.$cvc=this.$(".cvc input"),
				this.$expiryMonth=this.$(".expiry input.expiryMonth"),
				this.$expiryYear=this.$(".expiry input.expiryYear"),
				this.$message=this.$(".message"),
				this.$cust_name=$(".cust_name"),
				this.$cust_addr1=$(".cust_addr1"),
				this.$cust_zip=$(".cust_zip"),
				this
		},
		t.prototype.renderToken=function(t){
			return this.$token=e('<input type="hidden">'),
			this.$token.attr("name",this.options.tokenName),
			this.$token.val(t),
			this.$el.html(this.$token)
		},
		t.prototype.setForm=function(t){
			return this.$form=e(t),
			this.$form.bind("submit.payment",this.submit)
		},
		t.prototype.setKey=function(e){
			return this.key=e,Stripe.setPublishableKey(this.key)
		},
		t.prototype.validate=function(){
			var e,t;return t=!0,this.$("div").removeClass("invalid"),
			this.$message.empty(),
			Stripe.validateCardNumber(this.$number.val())||(t=!1,this.handleError({code:"invalid_number"})),
			e=this.expiryVal(),
			Stripe.validateExpiry(e.month,e.year)||(t=!1,this.handleError({code:"expired_card"})),
			this.options.cvc&&!Stripe.validateCVC(this.$cvc.val())&&(t=!1,this.handleError({code:"invalid_cvc"})),
			t||this.$(".invalid input:first").select(),
			t
		},
		t.prototype.createToken=function(e){
			var t,n,r=this;
			return t=function(t,n){
				return n.error?e(n.error):e(null,n)
			},
			n=this.expiryVal(),
			Stripe.createToken({
				number:this.$number.val(),
				exp_month: n.month,
        						exp_year: n.year,
				name:this.$cust_name.val(),
				cvc:this.$cvc.val()||null,
				address_line1:this.$cust_addr1.val(),
				address_zip:this.$cust_zip.val()
			},t)
		},
		t.prototype.submit=function(e){
			e!=null&&e.preventDefault(),
			e!=null&&e.stopImmediatePropagation();
			if(!this.validate())
				return;
			if(this.pending)
				return;
			return this.pending=!0,
			this.disableInputs(),
			this.trigger("pending"),
			this.$el.addClass("pending"),
			this.createToken(this.handleToken)
		},
		t.prototype.handleToken=function(e,t){
			return this.enableInputs(),
			this.trigger("complete"),
			this.$el.removeClass("pending"),
			this.pending=!1,e?this.handleError(e):(this.trigger("success",t),
			this.$el.addClass("success"),
			this.options.token&&this.renderToken(t.id),
			this.$form.unbind("submit.payment",this.submit),
			this.$form.submit())
		},
		t.prototype.formatNumber=function(e){
			var t,n,r;t=String.fromCharCode(e.which);
			if(!/^\d+$/.test(t))
				return;
			r=this.$number.val(),
			Stripe.cardType(r)==="American Express"?n=r.match(/^(\d{4}|\d{4}\s\d{6})$/):n=r.match(/(?:^|\s)(\d{4})$/);
			if(n)
				return this.$number.val(r+" ")
		},
		t.prototype.restrictNumeric=function(e){
			var t;
			return e.shiftKey||e.metaKey?!0:e.which===0?!0:(t=String.fromCharCode(e.which),!/[A-Za-z]/.test(t))
		},
		t.prototype.cardTypes={Visa:"visa","American Express":"amex",MasterCard:"mastercard",Discover:"discover",Unknown:"unknown"},
		t.prototype.changeCardType=function(e){
			var t,n,r,i;r=Stripe.cardType(this.$number.val());
			if(!this.$number.hasClass(r)){
				i=this.cardTypes;
				for(n in i)
					t=i[n],
					this.$number.removeClass(t);
					return this.$number.addClass(this.cardTypes[r])
			}
		},
		t.prototype.handleError=function(e){
			e.message&&this.$message.text(e.message);
			switch(e.code){
				case"card_declined":
					this.invalidInput(this.$number);
					break;
				case"invalid_number":
				case"incorrect_number":
					this.invalidInput(this.$number);
					break;
				case"invalid_expiry_month":
					this.invalidInput(this.$expiryMonth);
					break;
				case"invalid_expiry_year":
				case"expired_card":
					this.invalidInput(this.$expiryYear);
					break;
				case"invalid_cvc":
					this.invalidInput(this.$cvc)
			}
			return this.$("label.invalid:first input").select(),
			this.trigger("error",e),
			typeof console!="undefined"&&console!==null?console.error("Stripe error:",e):void 0
		},
		t.prototype.invalidInput=function(e){
			return e.parent().addClass("invalid"),
			this.trigger("invalid",[e.attr("name"),e])
		},
		t.prototype.expiryVal=function(){var e,t,n,r;return n=function(e){return e.replace(/^\s+|\s+$/g,"")},e=n(this.$expiryMonth.val()),r=n(this.$expiryYear.val()),r.length===2&&(t=(new Date).getFullYear(),t=t.toString().slice(0,2),r=t+r),{month:e,year:r}},t.prototype.enableInputs=function(){var t;return t=this.$el.add(this.$form).find(":input"),t.each(function(){var n,r;return n=e(this),t.attr("disabled",(r=n.data("olddisabled"))!=null?r:!1)})},t.prototype.disableInputs=function(){var t;return t=this.$el.add(this.$form).find(":input"),t.each(function(){var t;return t=e(this),t.data("olddisabled",t.attr("disabled")),t.attr("disabled",!0)})},t.prototype.trigger=function(){var e,t,n;return t=arguments[0],e=2<=arguments.length?i.call(arguments,1):[],(n=this.$el).trigger.apply(n,[""+t+".payment"].concat(i.call(e)))},t.prototype.$=function(t){return e(t,this.$el)},t}(),document.createElement("payment"),typeof module!="undefined"&&module!==null&&(module.exports=PaymentTag),t=this,t.Stripe?e(function(){return typeof PaymentTag.replaceTags=="function"?PaymentTag.replaceTags():void 0}):(n=document.createElement("script"),n.onload=n.onreadystatechange=function(){if(!t.Stripe)return;if(n.done)return;return n.done=!0,typeof PaymentTag.replaceTags=="function"?PaymentTag.replaceTags():void 0},n.src="https://js.stripe.com/v1/",e(function(){var e;return e=document.getElementsByTagName("script")[0],e!=null?e.parentNode.insertBefore(n,e):void 0}))}).call(this),function(){this.PaymentTag||(this.PaymentTag={}),this.PaymentTag.view=function(e){e||(e={});var t=[],n=function(e){var n=t,r;return t=[],e.call(this),r=t.join(""),t=n,i(r)},r=function(e){return e&&e.ecoSafe?e:typeof e!="undefined"&&e!=null?o(e):""},i,s=e.safe,o=e.escape;return i=e.safe=function(e){if(e&&e.ecoSafe)return e;if(typeof e=="undefined"||e==null)e="";var t=new String(e);return t.ecoSafe=!0,t},o||(o=e.escape=function(e){return(""+e).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;")}),function(){(function(){t.push('<span class="message"></span>\n\n<div class="number">\n  <label for="paymentNumber">Card number</label>\n\n  <input type="tel" id="paymentNumber" placeholder="4242 4242 4242 4242" autofocus required>\n</div>\n\n<div class="expiry">\n  <label for="paymentExpiryMonth">Expiry date <em>(mm/yy)</em></label>\n\n  <input class="expiryMonth" type="tel" id="paymentExpiryMonth" placeholder="mm" required>\n  <input class="expiryYear" type="tel" id="paymentExpiryYear" placeholder="yy" required>\n</div>\n\n'),this.options.cvc&&t.push('\n  <div class="cvc">\n    <label for="paymentCVC">Security code</label>\n    <input type="tel" id="paymentCVC" placeholder="123" maxlength="4" required>\n  </div>\n'),t.push("\n")}).call(this)}.call(e),e.safe=s,e.escape=o,t.join("")}}.call(this);