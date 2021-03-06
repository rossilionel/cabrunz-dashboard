/*! ========================================================================
 * Core v1.1.0
 * Copyright 2014 pampersdry
 * ========================================================================
 *
 * pampersdry@gmail.com
 *
 * This script will be use in my other projects too.
 * Your support ensure the continuity of this script and it projects.
 * ======================================================================== */

if (typeof jQuery === "undefined") { throw new Error("This application requires jQuery"); }

/* ========================================================================
 * BEGIN INCLUDING 3RD PARTY SCRIPT THAT WILL BE UTILIZE BY THIS SCRIPT.
 *
 * IMPORTANT : Do not delete this below script as it will break the template
 * behavior.
 * ========================================================================
 * Placeholders.js v3.0.2
 * Src : http://jamesallardice.github.io/Placeholders.js/
 * ======================================================================== */
(function(global){function addEventListener(elem,event,fn){if(elem.addEventListener)return elem.addEventListener(event,fn,false);if(elem.attachEvent)return elem.attachEvent("on"+event,fn)}function inArray(arr,item){var i,len;for(i=0,len=arr.length;i<len;i++)if(arr[i]===item)return true;return false}function moveCaret(elem,index){var range;if(elem.createTextRange){range=elem.createTextRange();range.move("character",index);range.select()}else if(elem.selectionStart){elem.focus();elem.setSelectionRange(index,
index)}}function changeType(elem,type){try{elem.type=type;return true}catch(e){return false}}global.Placeholders={Utils:{addEventListener:addEventListener,inArray:inArray,moveCaret:moveCaret,changeType:changeType}}})(this);
(function(global){var validTypes=["text","search","url","tel","email","password","number","textarea"],badKeys=[27,33,34,35,36,37,38,39,40,8,46],placeholderStyleColor="#ccc",placeholderClassName="placeholdersjs",classNameRegExp=new RegExp("(?:^|\\s)"+placeholderClassName+"(?!\\S)"),inputs,textareas,ATTR_CURRENT_VAL="data-placeholder-value",ATTR_ACTIVE="data-placeholder-active",ATTR_INPUT_TYPE="data-placeholder-type",ATTR_FORM_HANDLED="data-placeholder-submit",ATTR_EVENTS_BOUND="data-placeholder-bound",
ATTR_OPTION_FOCUS="data-placeholder-focus",ATTR_OPTION_LIVE="data-placeholder-live",ATTR_MAXLENGTH="data-placeholder-maxlength",test=document.createElement("input"),head=document.getElementsByTagName("head")[0],root=document.documentElement,Placeholders=global.Placeholders,Utils=Placeholders.Utils,hideOnInput,liveUpdates,keydownVal,styleElem,styleRules,placeholder,timer,form,elem,len,i;function noop(){}function safeActiveElement(){try{return document.activeElement}catch(err){}}function hidePlaceholder(elem,
keydownValue){var type,maxLength,valueChanged=!!keydownValue&&elem.value!==keydownValue,isPlaceholderValue=elem.value===elem.getAttribute(ATTR_CURRENT_VAL);if((valueChanged||isPlaceholderValue)&&elem.getAttribute(ATTR_ACTIVE)==="true"){elem.removeAttribute(ATTR_ACTIVE);elem.value=elem.value.replace(elem.getAttribute(ATTR_CURRENT_VAL),"");elem.className=elem.className.replace(classNameRegExp,"");maxLength=elem.getAttribute(ATTR_MAXLENGTH);if(parseInt(maxLength,10)>=0){elem.setAttribute("maxLength",
maxLength);elem.removeAttribute(ATTR_MAXLENGTH)}type=elem.getAttribute(ATTR_INPUT_TYPE);if(type)elem.type=type;return true}return false}function showPlaceholder(elem){var type,maxLength,val=elem.getAttribute(ATTR_CURRENT_VAL);if(elem.value===""&&val){elem.setAttribute(ATTR_ACTIVE,"true");elem.value=val;elem.className+=" "+placeholderClassName;maxLength=elem.getAttribute(ATTR_MAXLENGTH);if(!maxLength){elem.setAttribute(ATTR_MAXLENGTH,elem.maxLength);elem.removeAttribute("maxLength")}type=elem.getAttribute(ATTR_INPUT_TYPE);
if(type)elem.type="text";else if(elem.type==="password")if(Utils.changeType(elem,"text"))elem.setAttribute(ATTR_INPUT_TYPE,"password");return true}return false}function handleElem(node,callback){var handleInputsLength,handleTextareasLength,handleInputs,handleTextareas,elem,len,i;if(node&&node.getAttribute(ATTR_CURRENT_VAL))callback(node);else{handleInputs=node?node.getElementsByTagName("input"):inputs;handleTextareas=node?node.getElementsByTagName("textarea"):textareas;handleInputsLength=handleInputs?
handleInputs.length:0;handleTextareasLength=handleTextareas?handleTextareas.length:0;for(i=0,len=handleInputsLength+handleTextareasLength;i<len;i++){elem=i<handleInputsLength?handleInputs[i]:handleTextareas[i-handleInputsLength];callback(elem)}}}function disablePlaceholders(node){handleElem(node,hidePlaceholder)}function enablePlaceholders(node){handleElem(node,showPlaceholder)}function makeFocusHandler(elem){return function(){if(hideOnInput&&(elem.value===elem.getAttribute(ATTR_CURRENT_VAL)&&elem.getAttribute(ATTR_ACTIVE)===
"true"))Utils.moveCaret(elem,0);else hidePlaceholder(elem)}}function makeBlurHandler(elem){return function(){showPlaceholder(elem)}}function makeKeydownHandler(elem){return function(e){keydownVal=elem.value;if(elem.getAttribute(ATTR_ACTIVE)==="true")if(keydownVal===elem.getAttribute(ATTR_CURRENT_VAL)&&Utils.inArray(badKeys,e.keyCode)){if(e.preventDefault)e.preventDefault();return false}}}function makeKeyupHandler(elem){return function(){hidePlaceholder(elem,keydownVal);if(elem.value===""){elem.blur();
Utils.moveCaret(elem,0)}}}function makeClickHandler(elem){return function(){if(elem===safeActiveElement()&&(elem.value===elem.getAttribute(ATTR_CURRENT_VAL)&&elem.getAttribute(ATTR_ACTIVE)==="true"))Utils.moveCaret(elem,0)}}function makeSubmitHandler(form){return function(){disablePlaceholders(form)}}function newElement(elem){if(elem.form){form=elem.form;if(typeof form==="string")form=document.getElementById(form);if(!form.getAttribute(ATTR_FORM_HANDLED)){Utils.addEventListener(form,"submit",makeSubmitHandler(form));
form.setAttribute(ATTR_FORM_HANDLED,"true")}}Utils.addEventListener(elem,"focus",makeFocusHandler(elem));Utils.addEventListener(elem,"blur",makeBlurHandler(elem));if(hideOnInput){Utils.addEventListener(elem,"keydown",makeKeydownHandler(elem));Utils.addEventListener(elem,"keyup",makeKeyupHandler(elem));Utils.addEventListener(elem,"click",makeClickHandler(elem))}elem.setAttribute(ATTR_EVENTS_BOUND,"true");elem.setAttribute(ATTR_CURRENT_VAL,placeholder);if(hideOnInput||elem!==safeActiveElement())showPlaceholder(elem)}
Placeholders.nativeSupport=test.placeholder!==void 0;if(!Placeholders.nativeSupport){inputs=document.getElementsByTagName("input");textareas=document.getElementsByTagName("textarea");hideOnInput=root.getAttribute(ATTR_OPTION_FOCUS)==="false";liveUpdates=root.getAttribute(ATTR_OPTION_LIVE)!=="false";styleElem=document.createElement("style");styleElem.type="text/css";styleRules=document.createTextNode("."+placeholderClassName+" { color:"+placeholderStyleColor+"; }");if(styleElem.styleSheet)styleElem.styleSheet.cssText=
styleRules.nodeValue;else styleElem.appendChild(styleRules);head.insertBefore(styleElem,head.firstChild);for(i=0,len=inputs.length+textareas.length;i<len;i++){elem=i<inputs.length?inputs[i]:textareas[i-inputs.length];placeholder=elem.attributes.placeholder;if(placeholder){placeholder=placeholder.nodeValue;if(placeholder&&Utils.inArray(validTypes,elem.type))newElement(elem)}}timer=setInterval(function(){for(i=0,len=inputs.length+textareas.length;i<len;i++){elem=i<inputs.length?inputs[i]:textareas[i-
inputs.length];placeholder=elem.attributes.placeholder;if(placeholder){placeholder=placeholder.nodeValue;if(placeholder&&Utils.inArray(validTypes,elem.type)){if(!elem.getAttribute(ATTR_EVENTS_BOUND))newElement(elem);if(placeholder!==elem.getAttribute(ATTR_CURRENT_VAL)||elem.type==="password"&&!elem.getAttribute(ATTR_INPUT_TYPE)){if(elem.type==="password"&&(!elem.getAttribute(ATTR_INPUT_TYPE)&&Utils.changeType(elem,"text")))elem.setAttribute(ATTR_INPUT_TYPE,"password");if(elem.value===elem.getAttribute(ATTR_CURRENT_VAL))elem.value=
placeholder;elem.setAttribute(ATTR_CURRENT_VAL,placeholder)}}}else if(elem.getAttribute(ATTR_ACTIVE)){hidePlaceholder(elem);elem.removeAttribute(ATTR_CURRENT_VAL)}}if(!liveUpdates)clearInterval(timer)},100)}Utils.addEventListener(global,"beforeunload",function(){Placeholders.disable()});Placeholders.disable=Placeholders.nativeSupport?noop:disablePlaceholders;Placeholders.enable=Placeholders.nativeSupport?noop:enablePlaceholders})(this);
(function($){var originalValFn=$.fn.val,originalPropFn=$.fn.prop;if(!Placeholders.nativeSupport){$.fn.val=function(val){var originalValue=originalValFn.apply(this,arguments),placeholder=this.eq(0).data("placeholder-value");if(val===undefined&&(this.eq(0).data("placeholder-active")&&originalValue===placeholder))return"";return originalValue};$.fn.prop=function(name,val){if(val===undefined&&(this.eq(0).data("placeholder-active")&&name==="value"))return"";return originalPropFn.apply(this,arguments)}}})(jQuery);

/* ========================================================================
 * FastClick.js v1.0.0
 * Src : https://github.com/ftlabs/fastclick
 * ======================================================================== */
function FastClick(layer){var oldOnClick;this.trackingClick=false;this.trackingClickStart=0;this.targetElement=null;this.touchStartX=0;this.touchStartY=0;this.lastTouchIdentifier=0;this.touchBoundary=10;this.layer=layer;if(FastClick.notNeeded(layer))return;function bind(method,context){return function(){return method.apply(context,arguments)}}if(deviceIsAndroid){layer.addEventListener("mouseover",bind(this.onMouse,this),true);layer.addEventListener("mousedown",bind(this.onMouse,this),true);layer.addEventListener("mouseup",
bind(this.onMouse,this),true)}layer.addEventListener("click",bind(this.onClick,this),true);layer.addEventListener("touchstart",bind(this.onTouchStart,this),false);layer.addEventListener("touchmove",bind(this.onTouchMove,this),false);layer.addEventListener("touchend",bind(this.onTouchEnd,this),false);layer.addEventListener("touchcancel",bind(this.onTouchCancel,this),false);if(!Event.prototype.stopImmediatePropagation){layer.removeEventListener=function(type,callback,capture){var rmv=Node.prototype.removeEventListener;
if(type==="click")rmv.call(layer,type,callback.hijacked||callback,capture);else rmv.call(layer,type,callback,capture)};layer.addEventListener=function(type,callback,capture){var adv=Node.prototype.addEventListener;if(type==="click")adv.call(layer,type,callback.hijacked||(callback.hijacked=function(event){if(!event.propagationStopped)callback(event)}),capture);else adv.call(layer,type,callback,capture)}}if(typeof layer.onclick==="function"){oldOnClick=layer.onclick;layer.addEventListener("click",function(event){oldOnClick(event)},
false);layer.onclick=null}}var deviceIsAndroid=navigator.userAgent.indexOf("Android")>0;var deviceIsIOS=/iP(ad|hone|od)/.test(navigator.userAgent);var deviceIsIOS4=deviceIsIOS&&/OS 4_\d(_\d)?/.test(navigator.userAgent);var deviceIsIOSWithBadTarget=deviceIsIOS&&/OS ([6-9]|\d{2})_\d/.test(navigator.userAgent);
FastClick.prototype.needsClick=function(target){switch(target.nodeName.toLowerCase()){case "button":case "select":case "textarea":if(target.disabled)return true;break;case "input":if(deviceIsIOS&&target.type==="file"||target.disabled)return true;break;case "label":case "video":return true}return/\bneedsclick\b/.test(target.className)};
FastClick.prototype.needsFocus=function(target){switch(target.nodeName.toLowerCase()){case "textarea":return true;case "select":return!deviceIsAndroid;case "input":switch(target.type){case "button":case "checkbox":case "file":case "image":case "radio":case "submit":return false}return!target.disabled&&!target.readOnly;default:return/\bneedsfocus\b/.test(target.className)}};
FastClick.prototype.sendClick=function(targetElement,event){var clickEvent,touch;if(document.activeElement&&document.activeElement!==targetElement)document.activeElement.blur();touch=event.changedTouches[0];clickEvent=document.createEvent("MouseEvents");clickEvent.initMouseEvent(this.determineEventType(targetElement),true,true,window,1,touch.screenX,touch.screenY,touch.clientX,touch.clientY,false,false,false,false,0,null);clickEvent.forwardedTouchEvent=true;targetElement.dispatchEvent(clickEvent)};
FastClick.prototype.determineEventType=function(targetElement){if(deviceIsAndroid&&targetElement.tagName.toLowerCase()==="select")return"mousedown";return"click"};FastClick.prototype.focus=function(targetElement){var length;if(deviceIsIOS&&(targetElement.setSelectionRange&&(targetElement.type.indexOf("date")!==0&&targetElement.type!=="time"))){length=targetElement.value.length;targetElement.setSelectionRange(length,length)}else targetElement.focus()};
FastClick.prototype.updateScrollParent=function(targetElement){var scrollParent,parentElement;scrollParent=targetElement.fastClickScrollParent;if(!scrollParent||!scrollParent.contains(targetElement)){parentElement=targetElement;do{if(parentElement.scrollHeight>parentElement.offsetHeight){scrollParent=parentElement;targetElement.fastClickScrollParent=parentElement;break}parentElement=parentElement.parentElement}while(parentElement)}if(scrollParent)scrollParent.fastClickLastScrollTop=scrollParent.scrollTop};
FastClick.prototype.getTargetElementFromEventTarget=function(eventTarget){if(eventTarget.nodeType===Node.TEXT_NODE)return eventTarget.parentNode;return eventTarget};
FastClick.prototype.onTouchStart=function(event){var targetElement,touch,selection;if(event.targetTouches.length>1)return true;targetElement=this.getTargetElementFromEventTarget(event.target);touch=event.targetTouches[0];if(deviceIsIOS){selection=window.getSelection();if(selection.rangeCount&&!selection.isCollapsed)return true;if(!deviceIsIOS4){if(touch.identifier===this.lastTouchIdentifier){event.preventDefault();return false}this.lastTouchIdentifier=touch.identifier;this.updateScrollParent(targetElement)}}this.trackingClick=
true;this.trackingClickStart=event.timeStamp;this.targetElement=targetElement;this.touchStartX=touch.pageX;this.touchStartY=touch.pageY;if(event.timeStamp-this.lastClickTime<200)event.preventDefault();return true};FastClick.prototype.touchHasMoved=function(event){var touch=event.changedTouches[0],boundary=this.touchBoundary;if(Math.abs(touch.pageX-this.touchStartX)>boundary||Math.abs(touch.pageY-this.touchStartY)>boundary)return true;return false};
FastClick.prototype.onTouchMove=function(event){if(!this.trackingClick)return true;if(this.targetElement!==this.getTargetElementFromEventTarget(event.target)||this.touchHasMoved(event)){this.trackingClick=false;this.targetElement=null}return true};FastClick.prototype.findControl=function(labelElement){if(labelElement.control!==undefined)return labelElement.control;if(labelElement.htmlFor)return document.getElementById(labelElement.htmlFor);return labelElement.querySelector("button, input:not([type=hidden]), keygen, meter, output, progress, select, textarea")};
FastClick.prototype.onTouchEnd=function(event){var forElement,trackingClickStart,targetTagName,scrollParent,touch,targetElement=this.targetElement;if(!this.trackingClick)return true;if(event.timeStamp-this.lastClickTime<200){this.cancelNextClick=true;return true}this.cancelNextClick=false;this.lastClickTime=event.timeStamp;trackingClickStart=this.trackingClickStart;this.trackingClick=false;this.trackingClickStart=0;if(deviceIsIOSWithBadTarget){touch=event.changedTouches[0];targetElement=document.elementFromPoint(touch.pageX-
window.pageXOffset,touch.pageY-window.pageYOffset)||targetElement;targetElement.fastClickScrollParent=this.targetElement.fastClickScrollParent}targetTagName=targetElement.tagName.toLowerCase();if(targetTagName==="label"){forElement=this.findControl(targetElement);if(forElement){this.focus(targetElement);if(deviceIsAndroid)return false;targetElement=forElement}}else if(this.needsFocus(targetElement)){if(event.timeStamp-trackingClickStart>100||deviceIsIOS&&(window.top!==window&&targetTagName==="input")){this.targetElement=
null;return false}this.focus(targetElement);this.sendClick(targetElement,event);if(!deviceIsIOS4||targetTagName!=="select"){this.targetElement=null;event.preventDefault()}return false}if(deviceIsIOS&&!deviceIsIOS4){scrollParent=targetElement.fastClickScrollParent;if(scrollParent&&scrollParent.fastClickLastScrollTop!==scrollParent.scrollTop)return true}if(!this.needsClick(targetElement)){event.preventDefault();this.sendClick(targetElement,event)}return false};
FastClick.prototype.onTouchCancel=function(){this.trackingClick=false;this.targetElement=null};FastClick.prototype.onMouse=function(event){if(!this.targetElement)return true;if(event.forwardedTouchEvent)return true;if(!event.cancelable)return true;if(!this.needsClick(this.targetElement)||this.cancelNextClick){if(event.stopImmediatePropagation)event.stopImmediatePropagation();else event.propagationStopped=true;event.stopPropagation();event.preventDefault();return false}return true};
FastClick.prototype.onClick=function(event){var permitted;if(this.trackingClick){this.targetElement=null;this.trackingClick=false;return true}if(event.target.type==="submit"&&event.detail===0)return true;permitted=this.onMouse(event);if(!permitted)this.targetElement=null;return permitted};
FastClick.prototype.destroy=function(){var layer=this.layer;if(deviceIsAndroid){layer.removeEventListener("mouseover",this.onMouse,true);layer.removeEventListener("mousedown",this.onMouse,true);layer.removeEventListener("mouseup",this.onMouse,true)}layer.removeEventListener("click",this.onClick,true);layer.removeEventListener("touchstart",this.onTouchStart,false);layer.removeEventListener("touchmove",this.onTouchMove,false);layer.removeEventListener("touchend",this.onTouchEnd,false);layer.removeEventListener("touchcancel",
this.onTouchCancel,false)};
FastClick.notNeeded=function(layer){var metaViewport;var chromeVersion;if(typeof window.ontouchstart==="undefined")return true;chromeVersion=+(/Chrome\/([0-9]+)/.exec(navigator.userAgent)||[,0])[1];if(chromeVersion)if(deviceIsAndroid){metaViewport=document.querySelector("meta[name=viewport]");if(metaViewport){if(metaViewport.content.indexOf("user-scalable=no")!==-1)return true;if(chromeVersion>31&&window.innerWidth<=window.screen.width)return true}}else return true;if(layer.style.msTouchAction==="none")return true;
return false};FastClick.attach=function(layer){return new FastClick(layer)};if(typeof define!=="undefined"&&define.amd)define(function(){return FastClick});else if(typeof module!=="undefined"&&module.exports){module.exports=FastClick.attach;module.exports.FastClick=FastClick}else window.FastClick=FastClick;

/* ========================================================================
 * SlimScroll.js v1.3.2
 * Src : https://github.com/rochal/jQuery-slimScroll
 * ======================================================================== */
(function(f){jQuery.fn.extend({slimScroll:function(g){var a=f.extend({width:"auto",height:"250px",size:"7px",color:"#000",position:"right",distance:"1px",start:"top",opacity:0.4,alwaysVisible:!1,disableFadeOut:!1,railVisible:!1,railColor:"#333",railOpacity:0.2,railDraggable:!0,railClass:"slimScrollRail",barClass:"slimScrollBar",wrapperClass:"slimScrollDiv",allowPageScroll:!1,wheelStep:20,touchScrollStep:200,borderRadius:"7px",railBorderRadius:"7px"},g);this.each(function(){function u(d){if(r){d=d||
window.event;var c=0;d.wheelDelta&&(c=-d.wheelDelta/120);d.detail&&(c=d.detail/3);f(d.target||d.srcTarget||d.srcElement).closest("."+a.wrapperClass).is(b.parent())&&m(c,!0);d.preventDefault&&!k&&d.preventDefault();k||(d.returnValue=!1)}}function m(d,f,g){k=!1;var e=d,h=b.outerHeight()-c.outerHeight();f&&(e=parseInt(c.css("top"))+d*parseInt(a.wheelStep)/100*c.outerHeight(),e=Math.min(Math.max(e,0),h),e=0<d?Math.ceil(e):Math.floor(e),c.css({top:e+"px"}));l=parseInt(c.css("top"))/(b.outerHeight()-c.outerHeight());
e=l*(b[0].scrollHeight-b.outerHeight());g&&(e=d,d=e/b[0].scrollHeight*b.outerHeight(),d=Math.min(Math.max(d,0),h),c.css({top:d+"px"}));b.scrollTop(e);b.trigger("slimscrolling",~~e);v();p()}function C(){window.addEventListener?(this.addEventListener("DOMMouseScroll",u,!1),this.addEventListener("mousewheel",u,!1)):document.attachEvent("onmousewheel",u)}function w(){s=Math.max(b.outerHeight()/b[0].scrollHeight*b.outerHeight(),D);c.css({height:s+"px"});var a=s==b.outerHeight()?"none":"block";c.css({display:a})}
function v(){w();clearTimeout(A);l==~~l?(k=a.allowPageScroll,B!=l&&b.trigger("slimscroll",0==~~l?"top":"bottom")):k=!1;B=l;s>=b.outerHeight()?k=!0:(c.stop(!0,!0).fadeIn("fast"),a.railVisible&&h.stop(!0,!0).fadeIn("fast"))}function p(){a.alwaysVisible||(A=setTimeout(function(){a.disableFadeOut&&r||x||y||(c.fadeOut("slow"),h.fadeOut("slow"))},1E3))}var r,x,y,A,z,s,l,B,D=30,k=!1,b=f(this);if(b.parent().hasClass(a.wrapperClass)){var n=b.scrollTop(),c=b.parent().find("."+a.barClass),h=b.parent().find("."+
a.railClass);w();if(f.isPlainObject(g)){if("height"in g&&"auto"==g.height){b.parent().css("height","auto");b.css("height","auto");var q=b.parent().parent().height();b.parent().css("height",q);b.css("height",q)}if("scrollTo"in g)n=parseInt(a.scrollTo);else if("scrollBy"in g)n+=parseInt(a.scrollBy);else if("destroy"in g){c.remove();h.remove();b.unwrap();return}m(n,!1,!0)}}else{a.height="auto"==g.height?b.parent().height():g.height;n=f("<div></div>").addClass(a.wrapperClass).css({position:"relative",
overflow:"hidden",width:a.width,height:a.height});b.css({overflow:"hidden",width:a.width,height:a.height});var h=f("<div></div>").addClass(a.railClass).css({width:a.size,height:"100%",position:"absolute",top:0,display:a.alwaysVisible&&a.railVisible?"block":"none","border-radius":a.railBorderRadius,background:a.railColor,opacity:a.railOpacity,zIndex:90}),c=f("<div></div>").addClass(a.barClass).css({background:a.color,width:a.size,position:"absolute",top:0,opacity:a.opacity,display:a.alwaysVisible?
"block":"none","border-radius":a.borderRadius,BorderRadius:a.borderRadius,MozBorderRadius:a.borderRadius,WebkitBorderRadius:a.borderRadius,zIndex:99}),q="right"==a.position?{right:a.distance}:{left:a.distance};h.css(q);c.css(q);b.wrap(n);b.parent().append(c);b.parent().append(h);a.railDraggable&&c.bind("mousedown",function(a){var b=f(document);y=!0;t=parseFloat(c.css("top"));pageY=a.pageY;b.bind("mousemove.slimscroll",function(a){currTop=t+a.pageY-pageY;c.css("top",currTop);m(0,c.position().top,!1)});
b.bind("mouseup.slimscroll",function(a){y=!1;p();b.unbind(".slimscroll")});return!1}).bind("selectstart.slimscroll",function(a){a.stopPropagation();a.preventDefault();return!1});h.hover(function(){v()},function(){p()});c.hover(function(){x=!0},function(){x=!1});b.hover(function(){r=!0;v();p()},function(){r=!1;p()});b.bind("touchstart",function(a,b){a.originalEvent.touches.length&&(z=a.originalEvent.touches[0].pageY)});b.bind("touchmove",function(b){k||b.originalEvent.preventDefault();b.originalEvent.touches.length&&
(m((z-b.originalEvent.touches[0].pageY)/a.touchScrollStep,!0),z=b.originalEvent.touches[0].pageY)});w();"bottom"===a.start?(c.css({top:b.outerHeight()-c.outerHeight()}),m(0,!0)):"top"!==a.start&&(m(f(a.start).position().top,null,!0),a.alwaysVisible||c.hide());C()}});return this}});jQuery.fn.extend({slimscroll:jQuery.fn.slimScroll})})(jQuery);

/* ========================================================================
 * Unveil.js v??
 * Src : https://github.com/luis-almeida/unveil
 * ======================================================================== */
(function($){$.fn.unveil=function(threshold,callback){var $w=$(window),th=threshold||0,retina=window.devicePixelRatio>1,attrib=retina?"data-src-retina":"data-src",images=this,loaded;this.one("unveil",function(){var source=this.getAttribute(attrib);source=source||this.getAttribute("data-src");if(source){this.setAttribute("src",source);if(typeof callback==="function")callback.call(this)}});function unveil(){var inview=images.filter(function(){var $e=$(this);if($e.is(":hidden"))return;var wt=$w.scrollTop(),
wb=wt+$w.height(),et=$e.offset().top,eb=et+$e.height();return eb>=wt-th&&et<=wb+th});loaded=inview.trigger("unveil");images=images.not(loaded)}$w.scroll(unveil);$w.resize(unveil);unveil();return this}})(window.jQuery||window.Zepto);

/* ========================================================================
 * Waypoints.js v2.0.4
 * Src : https://github.com/imakewebthings/jquery-waypoints
 * ======================================================================== */
(function(){var __indexOf=[].indexOf||function(item){for(var i=0,l=this.length;i<l;i++)if(i in this&&this[i]===item)return i;return-1},__slice=[].slice;(function(root,factory){if(typeof define==="function"&&define.amd)return define("waypoints",["jquery"],function($){return factory($,root)});else return factory(root.jQuery,root)})(this,function($,window){var $w,Context,Waypoint,allWaypoints,contextCounter,contextKey,contexts,isTouch,jQMethods,methods,resizeEvent,scrollEvent,waypointCounter,waypointKey,
wp,wps;$w=$(window);isTouch=__indexOf.call(window,"ontouchstart")>=0;allWaypoints={horizontal:{},vertical:{}};contextCounter=1;contexts={};contextKey="waypoints-context-id";resizeEvent="resize.waypoints";scrollEvent="scroll.waypoints";waypointCounter=1;waypointKey="waypoints-waypoint-ids";wp="waypoint";wps="waypoints";Context=function(){function Context($element){var _this=this;this.$element=$element;this.element=$element[0];this.didResize=false;this.didScroll=false;this.id="context"+contextCounter++;
this.oldScroll={x:$element.scrollLeft(),y:$element.scrollTop()};this.waypoints={horizontal:{},vertical:{}};this.element[contextKey]=this.id;contexts[this.id]=this;$element.bind(scrollEvent,function(){var scrollHandler;if(!(_this.didScroll||isTouch)){_this.didScroll=true;scrollHandler=function(){_this.doScroll();return _this.didScroll=false};return window.setTimeout(scrollHandler,$[wps].settings.scrollThrottle)}});$element.bind(resizeEvent,function(){var resizeHandler;if(!_this.didResize){_this.didResize=
true;resizeHandler=function(){$[wps]("refresh");return _this.didResize=false};return window.setTimeout(resizeHandler,$[wps].settings.resizeThrottle)}})}Context.prototype.doScroll=function(){var axes,_this=this;axes={horizontal:{newScroll:this.$element.scrollLeft(),oldScroll:this.oldScroll.x,forward:"right",backward:"left"},vertical:{newScroll:this.$element.scrollTop(),oldScroll:this.oldScroll.y,forward:"down",backward:"up"}};if(isTouch&&(!axes.vertical.oldScroll||!axes.vertical.newScroll))$[wps]("refresh");
$.each(axes,function(aKey,axis){var direction,isForward,triggered;triggered=[];isForward=axis.newScroll>axis.oldScroll;direction=isForward?axis.forward:axis.backward;$.each(_this.waypoints[aKey],function(wKey,waypoint){var _ref,_ref1;if(axis.oldScroll<(_ref=waypoint.offset)&&_ref<=axis.newScroll)return triggered.push(waypoint);else if(axis.newScroll<(_ref1=waypoint.offset)&&_ref1<=axis.oldScroll)return triggered.push(waypoint)});triggered.sort(function(a,b){return a.offset-b.offset});if(!isForward)triggered.reverse();
return $.each(triggered,function(i,waypoint){if(waypoint.options.continuous||i===triggered.length-1)return waypoint.trigger([direction])})});return this.oldScroll={x:axes.horizontal.newScroll,y:axes.vertical.newScroll}};Context.prototype.refresh=function(){var axes,cOffset,isWin,_this=this;isWin=$.isWindow(this.element);cOffset=this.$element.offset();this.doScroll();axes={horizontal:{contextOffset:isWin?0:cOffset.left,contextScroll:isWin?0:this.oldScroll.x,contextDimension:this.$element.width(),oldScroll:this.oldScroll.x,
forward:"right",backward:"left",offsetProp:"left"},vertical:{contextOffset:isWin?0:cOffset.top,contextScroll:isWin?0:this.oldScroll.y,contextDimension:isWin?$[wps]("viewportHeight"):this.$element.height(),oldScroll:this.oldScroll.y,forward:"down",backward:"up",offsetProp:"top"}};return $.each(axes,function(aKey,axis){return $.each(_this.waypoints[aKey],function(i,waypoint){var adjustment,elementOffset,oldOffset,_ref,_ref1;adjustment=waypoint.options.offset;oldOffset=waypoint.offset;elementOffset=
$.isWindow(waypoint.element)?0:waypoint.$element.offset()[axis.offsetProp];if($.isFunction(adjustment))adjustment=adjustment.apply(waypoint.element);else if(typeof adjustment==="string"){adjustment=parseFloat(adjustment);if(waypoint.options.offset.indexOf("%")>-1)adjustment=Math.ceil(axis.contextDimension*adjustment/100)}waypoint.offset=elementOffset-axis.contextOffset+axis.contextScroll-adjustment;if(waypoint.options.onlyOnScroll&&oldOffset!=null||!waypoint.enabled)return;if(oldOffset!==null&&(oldOffset<
(_ref=axis.oldScroll)&&_ref<=waypoint.offset))return waypoint.trigger([axis.backward]);else if(oldOffset!==null&&(oldOffset>(_ref1=axis.oldScroll)&&_ref1>=waypoint.offset))return waypoint.trigger([axis.forward]);else if(oldOffset===null&&axis.oldScroll>=waypoint.offset)return waypoint.trigger([axis.forward])})})};Context.prototype.checkEmpty=function(){if($.isEmptyObject(this.waypoints.horizontal)&&$.isEmptyObject(this.waypoints.vertical)){this.$element.unbind([resizeEvent,scrollEvent].join(" "));
return delete contexts[this.id]}};return Context}();Waypoint=function(){function Waypoint($element,context,options){var idList,_ref;options=$.extend({},$.fn[wp].defaults,options);if(options.offset==="bottom-in-view")options.offset=function(){var contextHeight;contextHeight=$[wps]("viewportHeight");if(!$.isWindow(context.element))contextHeight=context.$element.height();return contextHeight-$(this).outerHeight()};this.$element=$element;this.element=$element[0];this.axis=options.horizontal?"horizontal":
"vertical";this.callback=options.handler;this.context=context;this.enabled=options.enabled;this.id="waypoints"+waypointCounter++;this.offset=null;this.options=options;context.waypoints[this.axis][this.id]=this;allWaypoints[this.axis][this.id]=this;idList=(_ref=this.element[waypointKey])!=null?_ref:[];idList.push(this.id);this.element[waypointKey]=idList}Waypoint.prototype.trigger=function(args){if(!this.enabled)return;if(this.callback!=null)this.callback.apply(this.element,args);if(this.options.triggerOnce)return this.destroy()};
Waypoint.prototype.disable=function(){return this.enabled=false};Waypoint.prototype.enable=function(){this.context.refresh();return this.enabled=true};Waypoint.prototype.destroy=function(){delete allWaypoints[this.axis][this.id];delete this.context.waypoints[this.axis][this.id];return this.context.checkEmpty()};Waypoint.getWaypointsByElement=function(element){var all,ids;ids=element[waypointKey];if(!ids)return[];all=$.extend({},allWaypoints.horizontal,allWaypoints.vertical);return $.map(ids,function(id){return all[id]})};
return Waypoint}();methods={init:function(f,options){var _ref;if(options==null)options={};if((_ref=options.handler)==null)options.handler=f;this.each(function(){var $this,context,contextElement,_ref1;$this=$(this);contextElement=(_ref1=options.context)!=null?_ref1:$.fn[wp].defaults.context;if(!$.isWindow(contextElement))contextElement=$this.closest(contextElement);contextElement=$(contextElement);context=contexts[contextElement[0][contextKey]];if(!context)context=new Context(contextElement);return new Waypoint($this,
context,options)});$[wps]("refresh");return this},disable:function(){return methods._invoke.call(this,"disable")},enable:function(){return methods._invoke.call(this,"enable")},destroy:function(){return methods._invoke.call(this,"destroy")},prev:function(axis,selector){return methods._traverse.call(this,axis,selector,function(stack,index,waypoints){if(index>0)return stack.push(waypoints[index-1])})},next:function(axis,selector){return methods._traverse.call(this,axis,selector,function(stack,index,
waypoints){if(index<waypoints.length-1)return stack.push(waypoints[index+1])})},_traverse:function(axis,selector,push){var stack,waypoints;if(axis==null)axis="vertical";if(selector==null)selector=window;waypoints=jQMethods.aggregate(selector);stack=[];this.each(function(){var index;index=$.inArray(this,waypoints[axis]);return push(stack,index,waypoints[axis])});return this.pushStack(stack)},_invoke:function(method){this.each(function(){var waypoints;waypoints=Waypoint.getWaypointsByElement(this);
return $.each(waypoints,function(i,waypoint){waypoint[method]();return true})});return this}};$.fn[wp]=function(){var args,method;method=arguments[0],args=2<=arguments.length?__slice.call(arguments,1):[];if(methods[method])return methods[method].apply(this,args);else if($.isFunction(method))return methods.init.apply(this,arguments);else if($.isPlainObject(method))return methods.init.apply(this,[null,method]);else if(!method)return $.error("jQuery Waypoints needs a callback function or handler option.");
else return $.error("The "+method+" method does not exist in jQuery Waypoints.")};$.fn[wp].defaults={context:window,continuous:true,enabled:true,horizontal:false,offset:0,triggerOnce:false};jQMethods={refresh:function(){return $.each(contexts,function(i,context){return context.refresh()})},viewportHeight:function(){var _ref;return(_ref=window.innerHeight)!=null?_ref:$w.height()},aggregate:function(contextSelector){var collection,waypoints,_ref;collection=allWaypoints;if(contextSelector)collection=
(_ref=contexts[$(contextSelector)[0][contextKey]])!=null?_ref.waypoints:void 0;if(!collection)return[];waypoints={horizontal:[],vertical:[]};$.each(waypoints,function(axis,arr){$.each(collection[axis],function(key,waypoint){return arr.push(waypoint)});arr.sort(function(a,b){return a.offset-b.offset});waypoints[axis]=$.map(arr,function(waypoint){return waypoint.element});return waypoints[axis]=$.unique(waypoints[axis])});return waypoints},above:function(contextSelector){if(contextSelector==null)contextSelector=
window;return jQMethods._filter(contextSelector,"vertical",function(context,waypoint){return waypoint.offset<=context.oldScroll.y})},below:function(contextSelector){if(contextSelector==null)contextSelector=window;return jQMethods._filter(contextSelector,"vertical",function(context,waypoint){return waypoint.offset>context.oldScroll.y})},left:function(contextSelector){if(contextSelector==null)contextSelector=window;return jQMethods._filter(contextSelector,"horizontal",function(context,waypoint){return waypoint.offset<=
context.oldScroll.x})},right:function(contextSelector){if(contextSelector==null)contextSelector=window;return jQMethods._filter(contextSelector,"horizontal",function(context,waypoint){return waypoint.offset>context.oldScroll.x})},enable:function(){return jQMethods._invoke("enable")},disable:function(){return jQMethods._invoke("disable")},destroy:function(){return jQMethods._invoke("destroy")},extendFn:function(methodName,f){return methods[methodName]=f},_invoke:function(method){var waypoints;waypoints=
$.extend({},allWaypoints.vertical,allWaypoints.horizontal);return $.each(waypoints,function(key,waypoint){waypoint[method]();return true})},_filter:function(selector,axis,test){var context,waypoints;context=contexts[$(selector)[0][contextKey]];if(!context)return[];waypoints=[];$.each(context.waypoints[axis],function(i,waypoint){if(test(context,waypoint))return waypoints.push(waypoint)});waypoints.sort(function(a,b){return a.offset-b.offset});return $.map(waypoints,function(waypoint){return waypoint.element})}};
$[wps]=function(){var args,method;method=arguments[0],args=2<=arguments.length?__slice.call(arguments,1):[];if(jQMethods[method])return jQMethods[method].apply(null,args);else return jQMethods.aggregate.call(null,method)};$[wps].settings={resizeThrottle:100,scrollThrottle:30};return $w.load(function(){return $[wps]("refresh")})})}).call(this);

/* ========================================================================
 * NProgress.js v0.1.2
 * Src : http://ricostacruz.com/nprogress
 * ======================================================================== */
(function(factory){if(typeof module==="function")module.exports=factory(this.jQuery||require("jquery"));else if(typeof define==="function"&&define.amd)define(["jquery"],function($){return factory($)});else this.NProgress=factory(this.jQuery)})(function($){var NProgress={};NProgress.version="0.1.2";var Settings=NProgress.settings={minimum:0.08,easing:"ease",positionUsing:"",speed:200,trickle:true,trickleRate:0.02,trickleSpeed:800,showSpinner:true,template:'<div class="bar" role="bar"><div class="peg"></div></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'};
NProgress.configure=function(options){$.extend(Settings,options);return this};NProgress.status=null;NProgress.set=function(n){var started=NProgress.isStarted();n=clamp(n,Settings.minimum,1);NProgress.status=n===1?null:n;var $progress=NProgress.render(!started),$bar=$progress.find('[role="bar"]'),speed=Settings.speed,ease=Settings.easing;$progress[0].offsetWidth;$progress.queue(function(next){if(Settings.positionUsing==="")Settings.positionUsing=NProgress.getPositioningCSS();$bar.css(barPositionCSS(n,
speed,ease));if(n===1){$progress.css({transition:"none",opacity:1});$progress[0].offsetWidth;setTimeout(function(){$progress.css({transition:"all "+speed+"ms linear",opacity:0});setTimeout(function(){NProgress.remove();next()},speed)},speed)}else setTimeout(next,speed)});return this};NProgress.isStarted=function(){return typeof NProgress.status==="number"};NProgress.start=function(){if(!NProgress.status)NProgress.set(0);var work=function(){setTimeout(function(){if(!NProgress.status)return;NProgress.trickle();
work()},Settings.trickleSpeed)};if(Settings.trickle)work();return this};NProgress.done=function(force){if(!force&&!NProgress.status)return this;return NProgress.inc(0.3+0.5*Math.random()).set(1)};NProgress.inc=function(amount){var n=NProgress.status;if(!n)return NProgress.start();else{if(typeof amount!=="number")amount=(1-n)*clamp(Math.random()*n,0.1,0.95);n=clamp(n+amount,0,0.994);return NProgress.set(n)}};NProgress.trickle=function(){return NProgress.inc(Math.random()*Settings.trickleRate)};(function(){var initial=
0,current=0;NProgress.promise=function($promise){if(!$promise||$promise.state()=="resolved")return this;if(current==0)NProgress.start();initial++;current++;$promise.always(function(){current--;if(current==0){initial=0;NProgress.done()}else NProgress.set((initial-current)/initial)});return this}})();NProgress.render=function(fromStart){if(NProgress.isRendered())return $("#nprogress");$("html").addClass("nprogress-busy");var $el=$("<div id='nprogress'>").html(Settings.template);var perc=fromStart?"-100":
toBarPerc(NProgress.status||0);$el.find('[role="bar"]').css({transition:"all 0 linear",transform:"translate3d("+perc+"%,0,0)"});if(!Settings.showSpinner)$el.find('[role="spinner"]').remove();$el.appendTo(document.body);return $el};NProgress.remove=function(){$("html").removeClass("nprogress-busy");$("#nprogress").remove()};NProgress.isRendered=function(){return $("#nprogress").length>0};NProgress.getPositioningCSS=function(){var bodyStyle=document.body.style;var vendorPrefix="WebkitTransform"in bodyStyle?
"Webkit":"MozTransform"in bodyStyle?"Moz":"msTransform"in bodyStyle?"ms":"OTransform"in bodyStyle?"O":"";if(vendorPrefix+"Perspective"in bodyStyle)return"translate3d";else if(vendorPrefix+"Transform"in bodyStyle)return"translate";else return"margin"};function clamp(n,min,max){if(n<min)return min;if(n>max)return max;return n}function toBarPerc(n){return(-1+n)*100}function barPositionCSS(n,speed,ease){var barCSS;if(Settings.positionUsing==="translate3d")barCSS={transform:"translate3d("+toBarPerc(n)+
"%,0,0)"};else if(Settings.positionUsing==="translate")barCSS={transform:"translate("+toBarPerc(n)+"%,0)"};else barCSS={"margin-left":toBarPerc(n)+"%"};barCSS.transition="all "+speed+"ms "+ease;return barCSS}return NProgress});

/* ========================================================================
 * Transit.js v0.9.9
 * Src : https://raw.githubusercontent.com/rstacruz/jquery.transit/
 * ======================================================================== */
(function($){$.transit={version:"0.9.9",propertyMap:{marginLeft:"margin",marginRight:"margin",marginBottom:"margin",marginTop:"margin",paddingLeft:"padding",paddingRight:"padding",paddingBottom:"padding",paddingTop:"padding"},enabled:true,useTransitionEnd:false};var div=document.createElement("div");var support={};function getVendorPropertyName(prop){if(prop in div.style)return prop;var prefixes=["Moz","Webkit","O","ms"];var prop_=prop.charAt(0).toUpperCase()+prop.substr(1);if(prop in div.style)return prop;
for(var i=0;i<prefixes.length;++i){var vendorProp=prefixes[i]+prop_;if(vendorProp in div.style)return vendorProp}}function checkTransform3dSupport(){div.style[support.transform]="";div.style[support.transform]="rotateY(90deg)";return div.style[support.transform]!==""}var isChrome=navigator.userAgent.toLowerCase().indexOf("chrome")>-1;support.transition=getVendorPropertyName("transition");support.transitionDelay=getVendorPropertyName("transitionDelay");support.transform=getVendorPropertyName("transform");
support.transformOrigin=getVendorPropertyName("transformOrigin");support.filter=getVendorPropertyName("Filter");support.transform3d=checkTransform3dSupport();var eventNames={"transition":"transitionEnd","MozTransition":"transitionend","OTransition":"oTransitionEnd","WebkitTransition":"webkitTransitionEnd","msTransition":"MSTransitionEnd"};var transitionEnd=support.transitionEnd=eventNames[support.transition]||null;for(var key in support)if(support.hasOwnProperty(key)&&typeof $.support[key]==="undefined")$.support[key]=
support[key];div=null;$.cssEase={"_default":"ease","in":"ease-in","out":"ease-out","in-out":"ease-in-out","snap":"cubic-bezier(0,1,.5,1)","easeOutCubic":"cubic-bezier(.215,.61,.355,1)","easeInOutCubic":"cubic-bezier(.645,.045,.355,1)","easeInCirc":"cubic-bezier(.6,.04,.98,.335)","easeOutCirc":"cubic-bezier(.075,.82,.165,1)","easeInOutCirc":"cubic-bezier(.785,.135,.15,.86)","easeInExpo":"cubic-bezier(.95,.05,.795,.035)","easeOutExpo":"cubic-bezier(.19,1,.22,1)","easeInOutExpo":"cubic-bezier(1,0,0,1)",
"easeInQuad":"cubic-bezier(.55,.085,.68,.53)","easeOutQuad":"cubic-bezier(.25,.46,.45,.94)","easeInOutQuad":"cubic-bezier(.455,.03,.515,.955)","easeInQuart":"cubic-bezier(.895,.03,.685,.22)","easeOutQuart":"cubic-bezier(.165,.84,.44,1)","easeInOutQuart":"cubic-bezier(.77,0,.175,1)","easeInQuint":"cubic-bezier(.755,.05,.855,.06)","easeOutQuint":"cubic-bezier(.23,1,.32,1)","easeInOutQuint":"cubic-bezier(.86,0,.07,1)","easeInSine":"cubic-bezier(.47,0,.745,.715)","easeOutSine":"cubic-bezier(.39,.575,.565,1)",
"easeInOutSine":"cubic-bezier(.445,.05,.55,.95)","easeInBack":"cubic-bezier(.6,-.28,.735,.045)","easeOutBack":"cubic-bezier(.175, .885,.32,1.275)","easeInOutBack":"cubic-bezier(.68,-.55,.265,1.55)"};$.cssHooks["transit:transform"]={get:function(elem){return $(elem).data("transform")||new Transform},set:function(elem,v){var value=v;if(!(value instanceof Transform))value=new Transform(value);if(support.transform==="WebkitTransform"&&!isChrome)elem.style[support.transform]=value.toString(true);else elem.style[support.transform]=
value.toString();$(elem).data("transform",value)}};$.cssHooks.transform={set:$.cssHooks["transit:transform"].set};$.cssHooks.filter={get:function(elem){return elem.style[support.filter]},set:function(elem,value){elem.style[support.filter]=value}};if($.fn.jquery<"1.8"){$.cssHooks.transformOrigin={get:function(elem){return elem.style[support.transformOrigin]},set:function(elem,value){elem.style[support.transformOrigin]=value}};$.cssHooks.transition={get:function(elem){return elem.style[support.transition]},
set:function(elem,value){elem.style[support.transition]=value}}}registerCssHook("scale");registerCssHook("translate");registerCssHook("rotate");registerCssHook("rotateX");registerCssHook("rotateY");registerCssHook("rotate3d");registerCssHook("perspective");registerCssHook("skewX");registerCssHook("skewY");registerCssHook("x",true);registerCssHook("y",true);function Transform(str){if(typeof str==="string")this.parse(str);return this}Transform.prototype={setFromString:function(prop,val){var args=typeof val===
"string"?val.split(","):val.constructor===Array?val:[val];args.unshift(prop);Transform.prototype.set.apply(this,args)},set:function(prop){var args=Array.prototype.slice.apply(arguments,[1]);if(this.setter[prop])this.setter[prop].apply(this,args);else this[prop]=args.join(",")},get:function(prop){if(this.getter[prop])return this.getter[prop].apply(this);else return this[prop]||0},setter:{rotate:function(theta){this.rotate=unit(theta,"deg")},rotateX:function(theta){this.rotateX=unit(theta,"deg")},rotateY:function(theta){this.rotateY=
unit(theta,"deg")},scale:function(x,y){if(y===undefined)y=x;this.scale=x+","+y},skewX:function(x){this.skewX=unit(x,"deg")},skewY:function(y){this.skewY=unit(y,"deg")},perspective:function(dist){this.perspective=unit(dist,"px")},x:function(x){this.set("translate",x,null)},y:function(y){this.set("translate",null,y)},translate:function(x,y){if(this._translateX===undefined)this._translateX=0;if(this._translateY===undefined)this._translateY=0;if(x!==null&&x!==undefined)this._translateX=unit(x,"px");if(y!==
null&&y!==undefined)this._translateY=unit(y,"px");this.translate=this._translateX+","+this._translateY}},getter:{x:function(){return this._translateX||0},y:function(){return this._translateY||0},scale:function(){var s=(this.scale||"1,1").split(",");if(s[0])s[0]=parseFloat(s[0]);if(s[1])s[1]=parseFloat(s[1]);return s[0]===s[1]?s[0]:s},rotate3d:function(){var s=(this.rotate3d||"0,0,0,0deg").split(",");for(var i=0;i<=3;++i)if(s[i])s[i]=parseFloat(s[i]);if(s[3])s[3]=unit(s[3],"deg");return s}},parse:function(str){var self=
this;str.replace(/([a-zA-Z0-9]+)\((.*?)\)/g,function(x,prop,val){self.setFromString(prop,val)})},toString:function(use3d){var re=[];for(var i in this)if(this.hasOwnProperty(i)){if(!support.transform3d&&(i==="rotateX"||(i==="rotateY"||(i==="perspective"||i==="transformOrigin"))))continue;if(i[0]!=="_")if(use3d&&i==="scale")re.push(i+"3d("+this[i]+",1)");else if(use3d&&i==="translate")re.push(i+"3d("+this[i]+",0)");else re.push(i+"("+this[i]+")")}return re.join(" ")}};function callOrQueue(self,queue,
fn){if(queue===true)self.queue(fn);else if(queue)self.queue(queue,fn);else fn()}function getProperties(props){var re=[];$.each(props,function(key){key=$.camelCase(key);key=$.transit.propertyMap[key]||($.cssProps[key]||key);key=uncamel(key);if(support[key])key=uncamel(support[key]);if($.inArray(key,re)===-1)re.push(key)});return re}function getTransition(properties,duration,easing,delay){var props=getProperties(properties);if($.cssEase[easing])easing=$.cssEase[easing];var attribs=""+toMS(duration)+
" "+easing;if(parseInt(delay,10)>0)attribs+=" "+toMS(delay);var transitions=[];$.each(props,function(i,name){transitions.push(name+" "+attribs)});return transitions.join(", ")}$.fn.transition=$.fn.transit=function(properties,duration,easing,callback){var self=this;var delay=0;var queue=true;var theseProperties=jQuery.extend(true,{},properties);if(typeof duration==="function"){callback=duration;duration=undefined}if(typeof duration==="object"){easing=duration.easing;delay=duration.delay||0;queue=duration.queue||
true;callback=duration.complete;duration=duration.duration}if(typeof easing==="function"){callback=easing;easing=undefined}if(typeof theseProperties.easing!=="undefined"){easing=theseProperties.easing;delete theseProperties.easing}if(typeof theseProperties.duration!=="undefined"){duration=theseProperties.duration;delete theseProperties.duration}if(typeof theseProperties.complete!=="undefined"){callback=theseProperties.complete;delete theseProperties.complete}if(typeof theseProperties.queue!=="undefined"){queue=
theseProperties.queue;delete theseProperties.queue}if(typeof theseProperties.delay!=="undefined"){delay=theseProperties.delay;delete theseProperties.delay}if(typeof duration==="undefined")duration=$.fx.speeds._default;if(typeof easing==="undefined")easing=$.cssEase._default;duration=toMS(duration);var transitionValue=getTransition(theseProperties,duration,easing,delay);var work=$.transit.enabled&&support.transition;var i=work?parseInt(duration,10)+parseInt(delay,10):0;if(i===0){var fn=function(next){self.css(theseProperties);
if(callback)callback.apply(self);if(next)next()};callOrQueue(self,queue,fn);return self}var oldTransitions={};var run=function(nextCall){var bound=false;var cb=function(){if(bound)self.unbind(transitionEnd,cb);if(i>0)self.each(function(){this.style[support.transition]=oldTransitions[this]||null});if(typeof callback==="function")callback.apply(self);if(typeof nextCall==="function")nextCall()};if(i>0&&(transitionEnd&&$.transit.useTransitionEnd)){bound=true;self.bind(transitionEnd,cb)}else window.setTimeout(cb,
i);self.each(function(){if(i>0)this.style[support.transition]=transitionValue;$(this).css(properties)})};var deferredRun=function(next){this.offsetWidth;run(next)};callOrQueue(self,queue,deferredRun);return this};function registerCssHook(prop,isPixels){if(!isPixels)$.cssNumber[prop]=true;$.transit.propertyMap[prop]=support.transform;$.cssHooks[prop]={get:function(elem){var t=$(elem).css("transit:transform");return t.get(prop)},set:function(elem,value){var t=$(elem).css("transit:transform");t.setFromString(prop,
value);$(elem).css({"transit:transform":t})}}}function uncamel(str){return str.replace(/([A-Z])/g,function(letter){return"-"+letter.toLowerCase()})}function unit(i,units){if(typeof i==="string"&&!i.match(/^[\-0-9\.]+$/))return i;else return""+i+units}function toMS(duration){var i=duration;if(typeof i==="string"&&!i.match(/^[\-0-9\.]+/))i=$.fx.speeds[i]||$.fx.speeds._default;return unit(i,"ms")}$.transit.getTransitionValue=getTransition})(jQuery);

/* ========================================================================
 * Response.js v0.8.0
 * Src : http://responsejs.com/
 * ======================================================================== */
(function(root,name,make){var $=root["jQuery"]||(root["Zepto"]||(root["ender"]||root["elo"]));if(typeof module!="undefined"&&module["exports"])module["exports"]=make($);else root[name]=make($)})(this,"Response",function($){if(typeof $!="function")try{return void console.warn("response.js aborted due to missing dependency")}catch(e){}var Response,root=this,name="Response",old=root[name],initContentKey="init"+name,win=window,doc=document,docElem=doc.documentElement,ready=$.domReady||$,$win=$(win),screen=
win.screen,AP=Array.prototype,OP=Object.prototype,push=AP.push,slice=AP.slice,concat=AP.concat,toString=OP.toString,owns=OP.hasOwnProperty,isArray=Array.isArray||function(item){return"[object Array]"===toString.call(item)},defaultPoints={width:[0,320,481,641,961,1025,1281],height:[0,481],ratio:[1,1.5,2]},Elemset,band,wave,device={},propTests={},isCustom={},sets={all:[]},suid=1,screenW=screen.width,screenH=screen.height,screenMax=screenW>screenH?screenW:screenH,screenMin=screenW+screenH-screenMax,
deviceW=function(){return screenW},deviceH=function(){return screenH},regexFunkyPunc=/[^a-z0-9_\-\.]/gi,regexTrimPunc=/^[\W\s]+|[\W\s]+$|/g,regexCamels=/([a-z])([A-Z])/g,regexDashB4=/-(.)/g,regexDataPrefix=/^data-(.+)$/,procreate=Object.create||function(parent){function Type(){}Type.prototype=parent;return new Type},namespaceIt=function(eventName,customNamespace){customNamespace=customNamespace||name;return eventName.replace(regexTrimPunc,"")+"."+customNamespace.replace(regexTrimPunc,"")},event={allLoaded:namespaceIt("allLoaded"),
crossover:namespaceIt("crossover")},matchMedia=win.matchMedia||win.msMatchMedia,media=matchMedia||function(){return{}},viewportW=function(){var a=docElem["clientWidth"],b=win["innerWidth"];return a<b?b:a},viewportH=function(){var a=docElem["clientHeight"],b=win["innerHeight"];return a<b?b:a};function doError(msg){throw new TypeError(msg?name+"."+msg:name);}function isNumber(item){return item===+item}function map(stack,fn,scope){for(var r=[],l=stack.length,i=0;i<l;)r[i]=fn.call(scope,stack[i],i++,
stack);return r}function compact(list){return!list?[]:sift(typeof list=="string"?list.split(" "):list)}function each(stack,fn,scope){if(null==stack)return stack;for(var l=stack.length,i=0;i<l;)fn.call(scope||stack[i],stack[i],i++,stack);return stack}function affix(stack,prefix,suffix){if(null==prefix)prefix="";if(null==suffix)suffix="";for(var r=[],l=stack.length,i=0;i<l;i++)null==stack[i]||r.push(prefix+stack[i]+suffix);return r}function sift(stack,fn,scope){var fail,l,v,r=[],u=0,i=0,run=typeof fn==
"function",not=true===scope;for(l=stack&&stack.length,scope=not?null:scope;i<l;i++){v=stack[i];fail=run?!fn.call(scope,v,i,stack):fn?typeof v!==fn:!v;fail===not&&(r[u++]=v)}return r}function merge(r,s){if(null==r||null==s)return r;if(typeof s=="object"&&isNumber(s.length))push.apply(r,sift(s,"undefined",true));else for(var k in s)owns.call(s,k)&&(void 0!==s[k]&&(r[k]=s[k]));return r}function route(item,fn,scope){if(null==item)return item;if(typeof item=="object"&&(!item.nodeType&&isNumber(item.length)))each(item,
fn,scope);else fn.call(scope||item,item);return item}function ranger(fn){return function(min,max){var point=fn();return point>=(min||0)&&(!max||point<=max)}}band=ranger(viewportW);wave=ranger(viewportH);device.band=ranger(deviceW);device.wave=ranger(deviceH);function dpr(decimal){var dPR=win.devicePixelRatio;if(null==decimal)return dPR||(dpr(2)?2:dpr(1.5)?1.5:dpr(1)?1:0);if(!isFinite(decimal))return false;if(dPR&&dPR>0)return dPR>=decimal;decimal="only all and (min--moz-device-pixel-ratio:"+decimal+
")";if(media(decimal).matches)return true;return!!media(decimal.replace("-moz-","")).matches}function camelize(s){return s.replace(regexDataPrefix,"$1").replace(regexDashB4,function(m,m1){return m1.toUpperCase()})}function datatize(s){return"data-"+(s?s.replace(regexDataPrefix,"$1").replace(regexCamels,"$1-$2").toLowerCase():s)}function render(s){var n;return typeof s!="string"||!s?s:"false"===s?false:"true"===s?true:"null"===s?null:"undefined"===s||((n=+s)||(0===n||"NaN"===s))?n:s}function getNative(e){return!e?
false:e.nodeType===1?e:e[0]&&e[0].nodeType===1?e[0]:false}function datasetChainable(key,value){var n,numOfArgs=arguments.length,elem=getNative(this),ret={},renderData=false;if(numOfArgs){if(isArray(key)){renderData=true;key=key[0]}if(typeof key==="string"){key=datatize(key);if(1===numOfArgs){ret=elem.getAttribute(key);return renderData?render(ret):ret}if(this===elem||2>(n=this.length||1))elem.setAttribute(key,value);else while(n--)n in this&&datasetChainable.apply(this[n],arguments)}else if(key instanceof
Object)for(n in key)key.hasOwnProperty(n)&&datasetChainable.call(this,n,key[n]);return this}if(elem.dataset&&typeof DOMStringMap!="undefined")return elem.dataset;each(elem.attributes,function(a){a&&((n=String(a.name).match(regexDataPrefix))&&(ret[camelize(n[1])]=a.value))});return ret}function deletesChainable(keys){if(this&&typeof keys==="string"){keys=compact(keys);route(this,function(el){each(keys,function(key){key&&el.removeAttribute(datatize(key))})})}return this}function dataset(elem){return datasetChainable.apply(elem,
slice.call(arguments,1))}function deletes(elem,keys){return deletesChainable.call(elem,keys)}function sel(keys){for(var k,r=[],i=0,l=keys.length;i<l;)(k=keys[i++])&&r.push("["+datatize(k.replace(regexTrimPunc,"").replace(".","\\."))+"]");return r.join()}function target(keys){return $(sel(compact(keys)))}function scrollX(){return window.pageXOffset||docElem.scrollLeft}function scrollY(){return window.pageYOffset||docElem.scrollTop}function rectangle(el,verge){var r=el.getBoundingClientRect?el.getBoundingClientRect():
{};verge=typeof verge=="number"?verge||0:0;return{top:(r.top||0)-verge,left:(r.left||0)-verge,bottom:(r.bottom||0)+verge,right:(r.right||0)+verge}}function inX(elem,verge){var r=rectangle(getNative(elem),verge);return!!r&&(r.right>=0&&r.left<=viewportW())}function inY(elem,verge){var r=rectangle(getNative(elem),verge);return!!r&&(r.bottom>=0&&r.top<=viewportH())}function inViewport(elem,verge){var r=rectangle(getNative(elem),verge);return!!r&&(r.bottom>=0&&(r.top<=viewportH()&&(r.right>=0&&r.left<=
viewportW())))}function detectMode(elem){var srcElems={img:1,input:1,source:3,embed:3,track:3,iframe:5,audio:5,video:5,script:5},modeID=srcElems[elem.nodeName.toLowerCase()]||-1;return 4>modeID?modeID:null!=elem.getAttribute("src")?5:-5}function store($elems,key,source){var valToStore;if(!$elems||null==key)doError("store");source=typeof source=="string"&&source;route($elems,function(el){if(source)valToStore=el.getAttribute(source);else if(0<detectMode(el))valToStore=el.getAttribute("src");else valToStore=
el.innerHTML;null==valToStore?deletes(el,key):dataset(el,key,valToStore)});return Response}function access(elem,keys){var ret=[];elem&&(keys&&each(compact(keys),function(k){ret.push(dataset(elem,k))},elem));return ret}function addTest(prop,fn){if(typeof prop=="string"&&typeof fn=="function"){propTests[prop]=fn;isCustom[prop]=1}return Response}Elemset=function(){var crossover=event.crossover,min=Math.min;function sanitize(key){return typeof key=="string"?key.toLowerCase().replace(regexFunkyPunc,""):
""}function ascending(a,b){return a-b}return{$e:0,mode:0,breakpoints:null,prefix:null,prop:"width",keys:[],dynamic:null,custom:0,values:[],fn:0,verge:null,newValue:0,currValue:1,aka:null,lazy:null,i:0,uid:null,reset:function(){var subjects=this.breakpoints,i=subjects.length,tempIndex=0;while(!tempIndex&&i--)this.fn(subjects[i])&&(tempIndex=i);if(tempIndex!==this.i){$win.trigger(crossover).trigger(this.prop+crossover);this.i=tempIndex||0}return this},configure:function(options){merge(this,options);
var i,points,prefix,aliases,aliasKeys,isNumeric=true,prop=this.prop;this.uid=suid++;if(null==this.verge)this.verge=min(screenMax,500);this.fn=propTests[prop]||doError("create @fn");if(null==this.dynamic)this.dynamic="device"!==prop.slice(0,6);this.custom=isCustom[prop];prefix=this.prefix?sift(map(compact(this.prefix),sanitize)):["min-"+prop+"-"];aliases=1<prefix.length?prefix.slice(1):0;this.prefix=prefix[0];points=this.breakpoints;if(isArray(points)){each(points,function(v){if(!v&&v!==0)throw"invalid breakpoint";
isNumeric=isNumeric&&isFinite(v)});isNumeric&&points.sort(ascending);points.length||doError("create @breakpoints")}else points=defaultPoints[prop]||(defaultPoints[prop.split("-").pop()]||doError("create @prop"));this.breakpoints=isNumeric?sift(points,function(n){return n<=screenMax}):points;this.keys=affix(this.breakpoints,this.prefix);this.aka=null;if(aliases){aliasKeys=[];i=aliases.length;while(i--)aliasKeys.push(affix(this.breakpoints,aliases[i]));this.aka=aliasKeys;this.keys=concat.apply(this.keys,
aliasKeys)}sets.all=sets.all.concat(sets[this.uid]=this.keys);return this},target:function(){this.$e=$(sel(sets[this.uid]));store(this.$e,initContentKey);this.keys.push(initContentKey);return this},decideValue:function(){var val=null,subjects=this.breakpoints,sL=subjects.length,i=sL;while(val==null&&i--)this.fn(subjects[i])&&(val=this.values[i]);this.newValue=typeof val==="string"?val:this.values[sL];return this},prepareData:function(elem){this.$e=$(elem);this.mode=detectMode(elem);this.values=access(this.$e,
this.keys);if(this.aka){var i=this.aka.length;while(i--)this.values=merge(this.values,access(this.$e,this.aka[i]))}return this.decideValue()},updateDOM:function(){if(this.currValue===this.newValue)return this;this.currValue=this.newValue;if(0<this.mode)this.$e[0].setAttribute("src",this.newValue);else if(null==this.newValue)this.$e.empty&&this.$e.empty();else if(this.$e.html)this.$e.html(this.newValue);else{this.$e.empty&&this.$e.empty();this.$e[0].innerHTML=this.newValue}return this}}}();propTests["width"]=
band;propTests["height"]=wave;propTests["device-width"]=device.band;propTests["device-height"]=device.wave;propTests["device-pixel-ratio"]=dpr;function resize(fn){$win.on("resize",fn);return Response}function crossover(prop,fn){var temp,eventToFire,eventCrossover=event.crossover;if(typeof prop=="function"){temp=fn;fn=prop;prop=temp}eventToFire=prop?""+prop+eventCrossover:eventCrossover;$win.on(eventToFire,fn);return Response}function action(fnOrArr){route(fnOrArr,function(fn){ready(fn);resize(fn)});
return Response}function create(args){route(args,function(options){typeof options=="object"||doError("create @args");var elemset=procreate(Elemset).configure(options),lowestNonZeroBP,verge=elemset.verge,breakpoints=elemset.breakpoints,scrollName=namespaceIt("scroll"),resizeName=namespaceIt("resize");if(!breakpoints.length)return;lowestNonZeroBP=breakpoints[0]||(breakpoints[1]||false);ready(function(){var allLoaded=event.allLoaded,lazy=!!elemset.lazy;function resizeHandler(){elemset.reset();each(elemset.$e,
function(el,i){elemset[i].decideValue().updateDOM()}).trigger(allLoaded)}function scrollHandler(){each(elemset.$e,function(el,i){inViewport(elemset[i].$e,verge)&&elemset[i].updateDOM()})}each(elemset.target().$e,function(el,i){elemset[i]=procreate(elemset).prepareData(el);if(!lazy||inViewport(elemset[i].$e,verge))elemset[i].updateDOM()});if(elemset.dynamic&&(elemset.custom||lowestNonZeroBP<screenMax))resize(resizeHandler,resizeName);if(!lazy)return;$win.on(scrollName,scrollHandler);elemset.$e.one(allLoaded,
function(){$win.off(scrollName,scrollHandler)})})});return Response}function noConflict(callback){if(root[name]===Response)root[name]=old;if(typeof callback=="function")callback.call(root,Response);return Response}function bridge(host,force){if(typeof host=="function"&&host.fn){if(force||void 0===host.fn.dataset)host.fn.dataset=datasetChainable;if(force||void 0===host.fn.deletes)host.fn.deletes=deletesChainable}return Response}Response={deviceMin:function(){return screenMin},deviceMax:function(){return screenMax},
noConflict:noConflict,bridge:bridge,create:create,addTest:addTest,datatize:datatize,camelize:camelize,render:render,store:store,access:access,target:target,object:procreate,crossover:crossover,action:action,resize:resize,ready:ready,affix:affix,sift:sift,dpr:dpr,deletes:deletes,scrollX:scrollX,scrollY:scrollY,deviceW:deviceW,deviceH:deviceH,device:device,inX:inX,inY:inY,route:route,merge:merge,media:media,wave:wave,band:band,map:map,each:each,inViewport:inViewport,dataset:dataset,viewportH:viewportH,
viewportW:viewportW};ready(function(){var settings=dataset(doc.body,"responsejs"),parse=win.JSON&&JSON.parse||$.parseJSON;settings=settings&&parse?parse(settings):settings;settings&&(settings.create&&create(settings.create));docElem.className=docElem.className.replace(/(^|\s)(no-)?responsejs(\s|$)/,"$1$3")+" responsejs "});return Response});

/* ========================================================================
 * mustache.js v??
 * Src : https://github.com/janl/mustache.js
 * ======================================================================== */
(function(root,factory){if(typeof exports==="object"&&exports)factory(exports);else{var mustache={};factory(mustache);if(typeof define==="function"&&define.amd)define(mustache);else root.Mustache=mustache}})(this,function(mustache){var RegExp_test=RegExp.prototype.test;function testRegExp(re,string){return RegExp_test.call(re,string)}var nonSpaceRe=/\S/;function isWhitespace(string){return!testRegExp(nonSpaceRe,string)}var Object_toString=Object.prototype.toString;var isArray=Array.isArray||function(object){return Object_toString.call(object)===
"[object Array]"};function isFunction(object){return typeof object==="function"}function escapeRegExp(string){return string.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g,"\\$&")}var entityMap={"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;","/":"&#x2F;"};function escapeHtml(string){return String(string).replace(/[&<>"'\/]/g,function(s){return entityMap[s]})}function escapeTags(tags){if(!isArray(tags)||tags.length!==2)throw new Error("Invalid tags: "+tags);return[new RegExp(escapeRegExp(tags[0])+
"\\s*"),new RegExp("\\s*"+escapeRegExp(tags[1]))]}var whiteRe=/\s*/;var spaceRe=/\s+/;var equalsRe=/\s*=/;var curlyRe=/\s*\}/;var tagRe=/#|\^|\/|>|\{|&|=|!/;function parseTemplate(template,tags){tags=tags||mustache.tags;template=template||"";if(typeof tags==="string")tags=tags.split(spaceRe);var tagRes=escapeTags(tags);var scanner=new Scanner(template);var sections=[];var tokens=[];var spaces=[];var hasTag=false;var nonSpace=false;function stripSpace(){if(hasTag&&!nonSpace)while(spaces.length)delete tokens[spaces.pop()];
else spaces=[];hasTag=false;nonSpace=false}var start,type,value,chr,token,openSection;while(!scanner.eos()){start=scanner.pos;value=scanner.scanUntil(tagRes[0]);if(value)for(var i=0,len=value.length;i<len;++i){chr=value.charAt(i);if(isWhitespace(chr))spaces.push(tokens.length);else nonSpace=true;tokens.push(["text",chr,start,start+1]);start+=1;if(chr==="\n")stripSpace()}if(!scanner.scan(tagRes[0]))break;hasTag=true;type=scanner.scan(tagRe)||"name";scanner.scan(whiteRe);if(type==="="){value=scanner.scanUntil(equalsRe);
scanner.scan(equalsRe);scanner.scanUntil(tagRes[1])}else if(type==="{"){value=scanner.scanUntil(new RegExp("\\s*"+escapeRegExp("}"+tags[1])));scanner.scan(curlyRe);scanner.scanUntil(tagRes[1]);type="&"}else value=scanner.scanUntil(tagRes[1]);if(!scanner.scan(tagRes[1]))throw new Error("Unclosed tag at "+scanner.pos);token=[type,value,start,scanner.pos];tokens.push(token);if(type==="#"||type==="^")sections.push(token);else if(type==="/"){openSection=sections.pop();if(!openSection)throw new Error('Unopened section "'+
value+'" at '+start);if(openSection[1]!==value)throw new Error('Unclosed section "'+openSection[1]+'" at '+start);}else if(type==="name"||(type==="{"||type==="&"))nonSpace=true;else if(type==="=")tagRes=escapeTags(tags=value.split(spaceRe))}openSection=sections.pop();if(openSection)throw new Error('Unclosed section "'+openSection[1]+'" at '+scanner.pos);return nestTokens(squashTokens(tokens))}function squashTokens(tokens){var squashedTokens=[];var token,lastToken;for(var i=0,len=tokens.length;i<len;++i){token=
tokens[i];if(token)if(token[0]==="text"&&(lastToken&&lastToken[0]==="text")){lastToken[1]+=token[1];lastToken[3]=token[3]}else{squashedTokens.push(token);lastToken=token}}return squashedTokens}function nestTokens(tokens){var nestedTokens=[];var collector=nestedTokens;var sections=[];var token,section;for(var i=0,len=tokens.length;i<len;++i){token=tokens[i];switch(token[0]){case "#":case "^":collector.push(token);sections.push(token);collector=token[4]=[];break;case "/":section=sections.pop();section[5]=
token[2];collector=sections.length>0?sections[sections.length-1][4]:nestedTokens;break;default:collector.push(token)}}return nestedTokens}function Scanner(string){this.string=string;this.tail=string;this.pos=0}Scanner.prototype.eos=function(){return this.tail===""};Scanner.prototype.scan=function(re){var match=this.tail.match(re);if(match&&match.index===0){var string=match[0];this.tail=this.tail.substring(string.length);this.pos+=string.length;return string}return""};Scanner.prototype.scanUntil=function(re){var index=
this.tail.search(re),match;switch(index){case -1:match=this.tail;this.tail="";break;case 0:match="";break;default:match=this.tail.substring(0,index);this.tail=this.tail.substring(index)}this.pos+=match.length;return match};function Context(view,parentContext){this.view=view==null?{}:view;this.cache={".":this.view};this.parent=parentContext}Context.prototype.push=function(view){return new Context(view,this)};Context.prototype.lookup=function(name){var value;if(name in this.cache)value=this.cache[name];
else{var context=this;while(context){if(name.indexOf(".")>0){value=context.view;var names=name.split("."),i=0;while(value!=null&&i<names.length)value=value[names[i++]]}else value=context.view[name];if(value!=null)break;context=context.parent}this.cache[name]=value}if(isFunction(value))value=value.call(this.view);return value};function Writer(){this.cache={}}Writer.prototype.clearCache=function(){this.cache={}};Writer.prototype.parse=function(template,tags){var cache=this.cache;var tokens=cache[template];
if(tokens==null)tokens=cache[template]=parseTemplate(template,tags);return tokens};Writer.prototype.render=function(template,view,partials){var tokens=this.parse(template);var context=view instanceof Context?view:new Context(view);return this.renderTokens(tokens,context,partials,template)};Writer.prototype.renderTokens=function(tokens,context,partials,originalTemplate){var buffer="";var self=this;function subRender(template){return self.render(template,context,partials)}var token,value;for(var i=
0,len=tokens.length;i<len;++i){token=tokens[i];switch(token[0]){case "#":value=context.lookup(token[1]);if(!value)continue;if(isArray(value))for(var j=0,jlen=value.length;j<jlen;++j)buffer+=this.renderTokens(token[4],context.push(value[j]),partials,originalTemplate);else if(typeof value==="object"||typeof value==="string")buffer+=this.renderTokens(token[4],context.push(value),partials,originalTemplate);else if(isFunction(value)){if(typeof originalTemplate!=="string")throw new Error("Cannot use higher-order sections without the original template");
value=value.call(context.view,originalTemplate.slice(token[3],token[5]),subRender);if(value!=null)buffer+=value}else buffer+=this.renderTokens(token[4],context,partials,originalTemplate);break;case "^":value=context.lookup(token[1]);if(!value||isArray(value)&&value.length===0)buffer+=this.renderTokens(token[4],context,partials,originalTemplate);break;case ">":if(!partials)continue;value=isFunction(partials)?partials(token[1]):partials[token[1]];if(value!=null)buffer+=this.renderTokens(this.parse(value),
context,partials,value);break;case "&":value=context.lookup(token[1]);if(value!=null)buffer+=value;break;case "name":value=context.lookup(token[1]);if(value!=null)buffer+=mustache.escape(value);break;case "text":buffer+=token[1];break}}return buffer};mustache.name="mustache.js";mustache.version="0.8.1";mustache.tags=["{{","}}"];var defaultWriter=new Writer;mustache.clearCache=function(){return defaultWriter.clearCache()};mustache.parse=function(template,tags){return defaultWriter.parse(template,tags)};
mustache.render=function(template,view,partials){return defaultWriter.render(template,view,partials)};mustache.to_html=function(template,view,partials,send){var result=mustache.render(template,view,partials);if(isFunction(send))send(result);else return result};mustache.escape=escapeHtml;mustache.Scanner=Scanner;mustache.Context=Context;mustache.Writer=Writer});

/* ========================================================================
 * BEGIN CORE SCRIPT
 *
 * IMPORTANT : This script will utilize all the above script. All this
 * template behavior and function depends on the above script
 * ======================================================================== */
;(function ($, window, document, undefined) {

    // Create the defaults once
    var pluginName  = "Core",
        isTouch     = "ontouchstart" in document.documentElement,
        isShrinked  = false,
        isScreensm  = false,
        defaults    = {
            console: false,
            eventPrefix: "fa",
            breakpoint: {
                "lg": 1200,
                "md": 992,
                "sm": 768,
                "xs": 480
            }
        };

    function MAIN(element, options) {
        this.element    = element;
        this.settings   = $.extend({}, defaults, options);
        this._defaults  = defaults;
        this._name      = pluginName;
        this._version   = "1.0.0";
        this.init();
    }

    MAIN.prototype = {
        init: function () {
            this.MISC.Init();
            this.PLUGINS();
        },
        // Helper
        // ================================
        HELPER: {
            // @Helper: Console
            // Per call
            // ================================
            Console: function (cevent) {
                if(settings.console) {
                    $(element).on(cevent, function (e, o) {
                        console.log("----- "+cevent+" -----");
                        console.log(o.element);
                    });
                }
            }
        },

        // Misc
        // ================================
        MISC: {
            // @MISC: Init
            Init: function () {
                this.ConsoleFix();
                isTouch ? "" : this.Scrollbar();
                this.Fastclick();
                this.Unveil();
                this.BsTooltip();
                this.BsPopover();
            },

            // @MISC: ConsoleFix
            // Per call
            // ================================
            ConsoleFix: function () {
                var method,
                    noop = function () {},
                    methods = [
                        "assert", "clear", "count", "debug", "dir", "dirxml", "error",
                        "exception", "group", "groupCollapsed", "groupEnd", "info", "log",
                        "markTimeline", "profile", "profileEnd", "table", "time", "timeEnd",
                        "timeStamp", "trace", "warn"
                    ],
                    length = methods.length,
                    console = (window.console = window.console || {});

                while (length--) {
                    method = methods[length];

                    // Only stub undefined methods.
                    if (!console[method]) {
                        console[method] = noop;
                    }
                }
            },

            // @MISC: Scrollbar
            // Per call
            // ================================
            Scrollbar: function () {
                $(".slimscroll").slimScroll({
                    size: "6px",
                    distance: "0px",
                    wrapperClass: "viewport",
                    railClass: "scrollrail",
                    barClass: "scrollbar",
                    wheelStep: 10,
                    railVisible: true,
                    alwaysVisible: true
                });
            },

            // @MISC: Fastclick
            // Per call
            // ================================
            Fastclick: function () {
                FastClick.attach(document.body);
            },

            // @MISC: Unveil - lazyload images
            // Per call
            // ================================
            Unveil: function () {
                $("[data-toggle~=unveil]").unveil(200, function () {
                    $(this).load(function () {
                        $(this).addClass("unveiled");
                    });
                });
            },

            // @MISC: BsTooltip - Bootstrap tooltip
            // Per call
            // ================================
            BsTooltip: function () {
                $("[data-toggle~=tooltip]").tooltip();
            },

            // @MISC: BsPopover - Bootstrap popover
            // Per call
            // ================================
            BsPopover: function () {
                $("[data-toggle~=popover]").popover();
            }
        },

        // Custom Mini Plugins
        // ================================
        PLUGINS: function () {
            // access MAIN variable
            element     = this.element;
            settings    = this.settings;

            // @PLUGIN: ToTop
            // Self invoking
            // ================================
            (function () {
                var toggler     = "[data-toggle~=totop]";

                // toggler
                $(element).on("click", toggler, function (e) {
                    $("html, body").animate({
                        scrollTop: 0
                    }, 200);

                    e.preventDefault();
                });
            })();

            // @PLUGIN: WayPoints
            // Self invoking
            // ================================
            (function () {
                var toggler     = "[data-toggle~=waypoints]";

                $(toggler).each(function () {
                    var wayShowAnimation,
                        wayHideAnimation,
                        wayOffset,
                        wayMarker,
                        target = this;

                    // check if marker is define or not
                    !!$(this).data("marker") ? wayMarker = $(this).data("marker") : wayMarker = this;

                    // check if offset is define or not
                    !!$(this).data("offset") ? wayOffset = $(this).data("offset") : wayOffset = "95%";

                    // check if show animation is define or not
                    !!$(this).data("showanim") ? wayShowAnimation = $(this).data("showanim") : wayShowAnimation = "fadeIn";

                    // check if hide animation is define or not
                    !!$(this).data("hideanim") ? wayHideAnimation = $(this).data("hideanim") : wayHideAnimation = false;

                    // waypoints core
                    $(wayMarker).waypoint(function (d) {
                        if(d === "down") {
                            $(target)
                                .removeClass(wayHideAnimation + " animated")
                                .addClass(wayShowAnimation + " animating")
                                .on('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function () {
                                    $(this).removeClass("animating").addClass("animated").removeClass(wayShowAnimation);;
                                });
                        } 
                        if( (d === "up") && (wayHideAnimation !== false)) {
                            $(target)
                                .removeClass(wayShowAnimation + " animated")
                                .addClass(wayHideAnimation + " animating")
                                .on('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function () {
                                    $(this).removeClass("animating").removeClass("animated").removeClass(wayHideAnimation);
                                });
                        }
                    }, { offset: wayOffset });
                });
            })();

            // @PLUGIN: SelectRow
            // Self invoking
            // ================================
            (function () {
                var contextual,
                    toggler     = "[data-toggle~=selectrow]",
                    target      = $(toggler).data("target");

                // check on DOM ready
                $(toggler).each(function () {
                    if($(this).is(":checked")) {
                        selectrow(this, "checked");
                    }
                });

                // clicker
                $(element).on("change", toggler, function () {
                    // checked / unchecked
                    if($(this).is(":checked")) {
                        selectrow(this, "checked");
                    } else {
                        selectrow(this, "unchecked");
                    }
                });

                // Core SelectRow function
                // state: checked/unchecked
                function selectrow ($this, state) {
                    // contextual
                    !!$($this).data("contextual") ? contextual = $($this).data("contextual") : contextual = "active";

                    if(state === "checked") {
                        // add contextual class
                        $($this).parents(target).addClass(contextual);

                        // publish event
                        $(element).trigger(settings.eventPrefix+".selectrow.selected", { "element": $($this).parents(target) });
                    } else {
                        // remove contextual class
                        $($this).parents(target).removeClass(contextual);

                        // publish event
                        $(element).trigger(settings.eventPrefix+".selectrow.unselected", { "element": $($this).parents(target) });
                    }
                }

                // Event console
                MAIN.prototype.HELPER.Console(settings.eventPrefix+".selectrow.selected");
                MAIN.prototype.HELPER.Console(settings.eventPrefix+".selectrow.unselected");
            })();

            // @PLUGIN: CheckAll
            // Self invoking
            // ================================
            (function () {
                var contextual,
                    toggler     = "[data-toggle~=checkall]",
                    target      = $(toggler).data("target");

                // check on DOM ready
                $(toggler).each(function () {
                    if($(this).is(":checked")) {
                        checked();
                    }
                });
                
                // clicker
                $(element).on("change", toggler, function () {
                    // checked / unchecked
                    if($(this).is(":checked")) {
                        checked();
                    } else {
                        unchecked();
                    }
                });

                // Core CheckAll function
                function checked () {
                    // find checkbox
                    $(target).find("input[type=checkbox]").each(function () {
                        $(this).prop("checked", true);

                        // select row
                        if($(this).data("toggle") === "selectrow") {
                            // contextual
                            !!$(this).data("contextual") ? contextual = $(this).data("contextual") : contextual = "active";

                            // add contextual class
                            $(this).parents($(this).data("target")).addClass(contextual);
                        }  
                    });

                    // publish event
                    $(element).trigger(settings.eventPrefix+".checkall.checked", { "element": $(target) });
                }
                
                function unchecked () {
                    // find checkbox
                    $(target).find("input[type=checkbox]").each(function () {
                        $(this).prop("checked", false);

                        // select row
                        if($(this).data("toggle") === "selectrow") {
                            // contextual
                            !!$(this).data("contextual") ? contextual = $(this).data("contextual") : contextual = "active";

                            // remove contextual class
                            $(this).parents($(this).data("target")).removeClass(contextual);
                        }
                    });

                    // publish event
                    $(element).trigger(settings.eventPrefix+".checkall.unchecked", { "element": $(target) });
                }

                // Event console
                MAIN.prototype.HELPER.Console(settings.eventPrefix+".checkall.checked");
                MAIN.prototype.HELPER.Console(settings.eventPrefix+".checkall.unchecked");
            })();

            // @PLUGIN: Panel Refresh
            // Self invoking
            // ================================
            (function () {
                var isDemo          = false,
                    indicatorClass  = "indicator",
                    toggler         = "[data-toggle~=panelrefresh]";

                // clicker
                $(element).on("click", toggler, function (e) {
                    // find panel element
                    var panel       = $(this).parents(".panel"),
                        indicator   = panel.find("."+indicatorClass);

                    // check if demo or not
                    !!$(this).hasClass("demo") ? isDemo = true : isDemo = false;

                    // check indicator
                    if(indicator.length !== 0) {
                        indicator.addClass("show");

                        // check if demo or not
                        if(isDemo) {
                            setTimeout(function () {
                                indicator.removeClass("show");
                            }, 2000);
                        }

                        // publish event
                        $(element).trigger(settings.eventPrefix+".panelrefresh.refresh", { "element": $(panel) });
                    } else {
                        $.error("There is no `indicator` element inside this panel.");
                    }

                    // prevent default
                    e.preventDefault();
                });

                // Event console
                MAIN.prototype.HELPER.Console(settings.eventPrefix+".panelrefresh.refresh");
            })();
            
            // @PLUGIN: Panel Collapse
            // Self invoking
            // ================================
            (function () {
                var toggler   = "[data-toggle~=panelcollapse]";

                // clicker
                $(element).on("click", toggler, function (e) {
                    // find panel element
                    var panel   = $(this).parents(".panel"),
                        target  = panel.children(".panel-collapse"),
                        height  = target.height();

                    // error handling
                    if(target.length === 0) {
                        $.error("collapsable element need to be wrap inside '.panel-collapse'");
                    }

                    // collapse the element
                    $(target).hasClass("out") ? close(this) : open(this);

                    function open (toggler) {
                        $(toggler).removeClass("down").addClass("up");
                        $(target)
                            .removeClass("pull").addClass("pulling")
                            .css("height", "0px")
                            .transition({ height: height }, function() {
                                $(this).removeClass("pulling").addClass("pull out");
                                $(this).css({ "height": "" });
                            });

                        // publish event
                        $(element).trigger(settings.eventPrefix+".panelcollapse.open", { "element": $(panel) });
                    }
                    function close (toggler) {
                        $(toggler).removeClass("up").addClass("down");
                        $(target)
                            .removeClass("pull out").addClass("pulling")
                            .css("height", height)
                            .transition({ height: "0px" }, function() {
                                $(this).removeClass("pulling").addClass("pull");
                                $(this).css({ "height": "" });
                            });

                        // publish event
                        $(element).trigger(settings.eventPrefix+".panelcollapse.close", { "element": $(panel) });
                    }

                    // prevent default
                    e.preventDefault();
                });

                // Event console
                MAIN.prototype.HELPER.Console(settings.eventPrefix+".panelcollapse.open");
                MAIN.prototype.HELPER.Console(settings.eventPrefix+".panelcollapse.close");
            })();
            
            // @PLUGIN: Panel Remove
            // Self invoking
            // ================================
            (function () {
                var panel,
                    parent,
                    handler   = "[data-toggle~=panelremove]";

                // clicker
                $(element).on("click", handler, function (e) {
                    // find panel element
                    panel   = $(this).parents(".panel");
                    parent  = $(this).data("parent");

                    // remove panel
                    panel.transition({ scale: 0 }, function () {
                        //remove
                        if(parent) {
                            $(this).parents(parent).remove();
                        } else {
                            $(this).remove();
                        }

                        // publish event
                        $(element).trigger(settings.eventPrefix+".panelcollapse.close", { "element": $(panel) });
                    });

                    // prevent default
                    e.preventDefault();
                });

                // Event console
                MAIN.prototype.HELPER.Console(settings.eventPrefix+".panelremove.remove");
            })();

            // @PLUGIN: SidebarShrink
            // Self invoking
            // ================================
            (function () {
                // define variable
                var shrinkHandler   = "[data-toggle~=minimize]";

                // core shrink function
                function toggleShrink (e) {
                    // toggle class
                    if($(element).hasClass("sidebar-minimized")) {
                        isShrinked = false;
                        $(element).removeClass("sidebar-minimized");
                        $(this).removeClass("minimized");

                        // publish event
                        $(element).trigger(settings.eventPrefix+".sidebar.maximize", { "element": $(element) });
                    } else {
                        isShrinked = true;
                        $(element).addClass("sidebar-minimized");
                        $(this).addClass("minimized");

                        // publish event
                        $(element).trigger(settings.eventPrefix+".sidebar.minimize", { "element": $(element) });
                    }

                    // prevent default
                    e.preventDefault();
                }

                $(document).on("click", shrinkHandler, toggleShrink);

                // Event console
                MAIN.prototype.HELPER.Console(settings.eventPrefix+".sidebar.minimize");
                MAIN.prototype.HELPER.Console(settings.eventPrefix+".sidebar.maximize");
            })();

            // @PLUGIN: SidebarMenu
            // Self invoking
            // utilize bootstrap collapse
            // ================================
            (function () {
                // define variable
                var menuHandler     = "[data-toggle~=menu]",
                    submenuHandler  = "[data-toggle~=submenu]";

                // setter
                Response.action(function () {
                    isScreensm = Response.band(settings.breakpoint.sm, settings.breakpoint.md-1);
                });

                // core toggle collapse
                function toggleCollapse (e) {
                    // click event handler
                    if(e.type === "click") {
                        var target  = $(this).data("target");

                        // toggle hide and show
                        if($(target).hasClass("in")) {
                            // hide the submenu
                            $(target).collapse("hide");
                            $(this).parent().removeClass("open");
                        } else {
                            // hide other showed target if parent is defined
                            if($(this).data("parent")) {
                                $($(this).data("parent")+" .in").each(function () {
                                    $(this).collapse("hide");
                                    $(this).parent().removeClass("open");
                                });
                            }

                            // show the submenu
                            $(target).collapse("show");
                            $(this).parent().addClass("open");
                        }
                    }
                }

                // core preserveSubmenu function
                function preserveSubmenu (e) {
                    // run only on tablet view and sidebar-menu collapse
                    if((isScreensm) || (isShrinked)) {
                        // define some variable
                        var target          = $(this).children(submenuHandler).data("target");
                        
                        // if have target
                        if(!!target === true) {
                            var viewport        = Response.viewportH(),
                                boundingRect    = $(target)[0].getBoundingClientRect(),
                                offsetTop       = boundingRect.bottom-viewport;

                            // mouseenter event handler
                            if(e.type === "mouseenter") {
                                // add arrow class
                                $(this).addClass("arrow");

                                if(boundingRect.bottom >= viewport) {
                                    $(target).css("top", "-"+offsetTop+"px");
                                }
                            }

                            // mouseleave event handler
                            if(e.type === "mouseleave") {
                                // add arrow class
                                $(this).removeClass("arrow");

                                $(target).css("top", "");
                            }
                        }
                    }
                }

                $(document)
                    .on("click", submenuHandler, toggleCollapse)
                    .on("mouseenter mouseleave", menuHandler+" > li", preserveSubmenu);
            })();

            // @PLUGIN: Offcanvas
            // Self invoking
            // ================================
            (function () {
                var direction,
                    sidebar,
                    toggler      = "[data-toggle~=offcanvas]",
                    openClass    = "sidebar-open";  

                // sidebar toggler
                function toggle () {
                    // get direction
                    direction = $(this).data("direction");
                    direction === "ltr" ? sidebar = ".sidebar-left" : sidebar = ".sidebar-right";

                    // trigger error if `data-direction` is not set
                    if((direction === false)||(direction === "")) {
                        $.error("missing `data-direction` value (ltr or rtl)");
                    }

                    // open/close sidebar
                    !$(element).hasClass(openClass+"-"+direction) ? open() : close();
                    return false;
                }

                function open () {
                    $(element).addClass(openClass+"-"+direction);
                    $(element).trigger(settings.eventPrefix+".offcanvas.open", { "element": $(sidebar) });
                }
                function close () {
                    if ($(element).hasClass(openClass+"-"+direction)) {
                        $(element).removeClass(openClass+"-"+direction);
                        $(element).trigger(settings.eventPrefix+".offcanvas.close", { "element": $(sidebar) });
                    }
                }

                $(document)
                    .on("click", close)
                    .on("click", ".sidebar,"+ toggler, function (e) { e.stopPropagation(); })
                    .on("click", toggler, toggle);

                // Event console
                MAIN.prototype.HELPER.Console(settings.eventPrefix+".offcanvas.open");
                MAIN.prototype.HELPER.Console(settings.eventPrefix+".offcanvas.close");
            })();

            // @PLUGIN: Ajax load
            // Self invoking
            // ================================
            (function () {
                var container,
                    source,
                    param,
                    eventClass   = {"complete": "ajaxload-complete", "start": "ajaxload-start", "success": "ajaxload-success", "error": "ajaxload-error"};
                    toggler      = "[data-toggle~=ajaxload]";  

                // clicker
                $(element).on("click", toggler, function (e) {
                    // check if container is define or nor
                    if($(this).data("container")) {
                        container = $($(this).data("container"));
                    } else {
                        $.error("missing `data-container`");
                    }

                    // remove active class
                    $(toggler).each(function () {
                        $(this).removeClass("active");
                    });
                    $(this).addClass("active");

                    // find source
                    $(this).data("source") ? source = $(this).data("source") : source = $(this).attr("href");

                    // add loading class
                    $(element)
                        .addClass(eventClass.start)
                        .removeClass(eventClass.complete)
                        .removeClass(eventClass.success)
                        .removeClass(eventClass.error);

                    // core ajax function
                    var ajaxload = $.ajax({
                        type: "GET",
                        url: source,
                        context: $(element)
                    });

                    // handle status (done)
                    ajaxload.done(function (data) {
                        $(this)
                            .addClass(eventClass.success)
                            .removeClass(eventClass.complete)
                            .removeClass(eventClass.start)
                            .removeClass(eventClass.error);

                        // append
                        container.html(data);

                        // publish event
                    });
                    // handle status (error)
                    ajaxload.error(function (data) {
                        $(this)
                            .addClass(eventClass.error)
                            .removeClass(eventClass.complete)
                            .removeClass(eventClass.start)
                            .removeClass(eventClass.success);
                    });
                    // handle status (complete)
                    ajaxload.always(function (data) {
                        $(this)
                            .addClass(eventClass.complete)
                            .removeClass(eventClass.start);
                    });

                    // prevent default
                    e.preventDefault();
                });

                // Event console
                //MAIN.prototype.HELPER.Console(settings.eventPrefix+".offcanvas.open");
                //MAIN.prototype.HELPER.Console(settings.eventPrefix+".offcanvas.close");
            })();
        }
    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, pluginName)) {
                $.data(this, pluginName, new MAIN(this, options));
            }
        });
    };

})(jQuery, window, document);