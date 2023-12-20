(()=>{"use strict";var e={94:(e,t,o)=>{o.d(t,{g:()=>r,i:()=>l});var n=o(907),a=o(523),i=o(667);const l=async(e,t)=>{const o=e.querySelector(".bigup__alert_output");if(o)return n.fF&&console.log(`${(0,n.P_)()} |START| alertsShowWaitHide | ${t[0]}`),(0,a.w)(e,!0),o.style.display="flex",await u(o,"opacity","0"),await(0,i.e)(o),await c(o,t),await u(o,"opacity","1"),"alert alertsShow complete"},r=async(e,t,o)=>{const l=e.querySelector(".bigup__alert_output");var r;if(l)return n.fF&&console.log(`${(0,n.P_)()} |START| alertsShowWaitHide | ${t[0]}`),(0,a.w)(e,!0),l.style.display="flex",await u(l,"opacity","0"),await(0,i.e)(l),await c(l,t),await u(l,"opacity","1"),await(r=o,new Promise((e=>{setTimeout((()=>{e("Pause completed successfully.")}),r)}))),await u(l,"opacity","0"),await(0,i.e)(l),l.style.display="none",(0,a.w)(e,!1),n.fF&&console.log(`${(0,n.P_)()} | END | alertsShowWaitHide | ${t[0]}`),"alert alertsShowWaitHide complete"};function s(e){return null!=e&&"function"==typeof e[Symbol.iterator]}function c(e,t){const o="bigup__alert",a={danger:"-danger",success:"-success",info:"-info",warning:"-warning"};return n.fF&&console.log(`${(0,n.P_)()} |START| popoutsIntoDom | ${t[0]}`),new Promise(((l,r)=>{try{if(!e||e.nodeType!==Node.ELEMENT_NODE)throw new TypeError("output must be an element node.");if(!s(t))throw new TypeError(`'alerts' must be non-string iterable. ${typeof t} found.`);let n=[];t.forEach((t=>{let l=document.createElement("p");l.innerText=(0,i.c)(t.text),[o,o+a[t.type]].forEach((e=>l.classList.add(e))),e.appendChild(l),n.push(l)})),l(n)}catch(e){r(e)}finally{n.fF&&console.log(`${(0,n.P_)()} | END | popoutsIntoDom | ${t[0]}`)}}))}function p(e,t){return new Promise(((o,a)=>{try{n.fF&&console.log(`${(0,n.P_)()} |START| transition | ${this.classList} : ${e} : ${t}`),this.style[e]=t;let a=setInterval((()=>{getComputedStyle(this).opacity===t&&(clearInterval(a),n.fF&&console.log(`${(0,n.P_)()} | END | transition | ${this.classList} : ${e} : ${t}`),o("Transition complete."))}),10)}catch(e){a(e)}}))}async function u(e,t,o){if(s(e)||(e=[e]),s(e)&&e.every((e=>1===e.nodeType))){const n=e.map((e=>p.bind(e)(t,o)));return await Promise.all(n)}throw new TypeError("elements must be a non-string iterable. "+typeof e+" found.")}},907:(e,t,o)=>{o.d(t,{BL:()=>i,P_:()=>l,fF:()=>n});let n=!1,a="";const i=()=>a=Date.now(),l=()=>(Date.now()-a).toString().padStart(5,"0")},426:(e,t,o)=>{o.d(t,{E:()=>i});var n=o(907),a=o(667);async function i(e,t){try{n.fF&&console.log(`${(0,n.P_)()} |START| Fetch request`);const o=new AbortController,a=setTimeout((()=>o.abort()),14e3),i=await fetch(e,{...t,signal:o.signal});clearTimeout(a);const l=await i.json();if(l.ok=i.ok,"string"==typeof l.output&&(l.output=[l.output]),!l.ok)throw l;return l}catch(e){e.output||(e.output=["Failed to establish a connection to the server."],e.ok=!1,console.error(e));for(const t in e.output)console.error((0,a.c)(e.output[t]));return e}finally{n.fF&&console.log(`${(0,n.P_)()} | END | Fetch request`)}}},466:(e,t,o)=>{o.d(t,{Q:()=>l,W:()=>c});var n=o(94),a=o(667);const i=["image/jpeg","image/png","image/gif","image/webp","image/heic","image/heif","image/avif","image/svg+xml","text/plain","application/pdf","application/vnd.oasis.opendocument.text","application/vnd.oasis.opendocument.spreadsheet","application/vnd.openxmlformats-officedocument.wordprocessingml.document","application/msword","application/vnd.ms-excel","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application/zip","application/vnd.rar"],l={detected:!1,list:[]},r=e=>{e.preventDefault();const t=e.currentTarget,o=t.closest(".bigup__customFileUpload").querySelector("input"),{files:n}=o,a=t.nextElementSibling.innerText,i=new DataTransfer;for(let e=0;e<n.length;e++){const t=n[e];t.name!==a&&i.items.add(t)}o.files=i.files,s(o)},s=async e=>{const{files:t}=e,o=e.closest(".bigup__customFileUpload"),s=o.querySelector(".bigup__customFileUpload_output"),c=e.closest("form"),p=document.createElement("ul"),u=o.querySelector("template");(0,a.e)(s),l.detected=!1,l.list=[];for(var d=0;d<t.length;++d){const e=t[d];let o="bigup__goodFileType";i.includes(e.type)||(l.detected=!0,l.list.push(e.name.split(".").pop()),o="bigup__badFileType");const n=document.createElement("li"),a=document.createElement("span"),c=document.createElement("button"),f=u.content.cloneNode(!0);c.appendChild(f),a.innerText=e.name,n.classList.add(o),n.appendChild(c),n.appendChild(a),p.appendChild(n),s.appendChild(p),c.addEventListener("click",r)}if(s.appendChild(p),l.detected){const e=[{text:`Files of type ".${l.list.join(", ")}" are not allowed`,type:"danger"}],t=5e3;await(0,n.g)(c,e,t)}},c=e=>{const t=e.currentTarget;s(t)}},523:(e,t,o)=>{o.d(t,{w:()=>a});var n=o(907);function a(e,t){const o=e.querySelectorAll(":is( input, textarea )"),a=e.querySelector(".bigup__form_submit");t?(n.fF&&console.log(`${(0,n.P_)()} |START| formLock | Locked`),e.classList.add("bigup__form-locked"),o.forEach((e=>{e.disabled=!0})),a.disabled=!0):(e.classList.remove("bigup__form-locked"),o.forEach((e=>{e.disabled=!1})),a.disabled=!1,n.fF&&console.log(`${(0,n.P_)()} | END | formLock | Unlocked`))}},870:(e,t,o)=>{o.d(t,{P:()=>c});var n=o(907),a=o(426),i=o(466),l=o(667),r=o(94);const s=bigupContactFormWpInlinedPublic;async function c(e){e.preventDefault(),n.fF&&(0,n.BL)(),n.fF&&console.log("Time | Start/Finish | Function | Target"),n.fF&&console.log((0,n.P_)()+" |START| handleSubmit");const t=e.currentTarget;""!==t.querySelector('[name="required_field"]').value&&(document.documentElement.remove(),window.location.replace("https://en.wikipedia.org/wiki/Robot"));const o=new FormData,c=t.querySelectorAll(":is( input, textarea )"),p=t.querySelector(".bigup__customFileUpload_input");if(c.forEach((e=>{o.append(e.name,e.value)})),p){if(i.Q.detected){const e=[{text:`Files of type ".${i.Q.list.join(", ")}" are not allowed. Please amend your file selection.`,type:"danger"}],o=5e3;return void await(0,r.g)(t,e,o)}{const e=p.files;for(let t=0;t<e.length;t++){let n=e[t];o.append("files[]",n,n.name)}}}const u=s.rest_url,d={method:"POST",headers:{"X-WP-Nonce":s.rest_nonce,Accept:"application/json"},body:o};try{const e=[{text:"Connecting...",type:"info"}];let[o]=await Promise.all([(0,a.E)(u,d),(0,r.i)(t,e)]);const n=[];if(o.output.forEach((e=>n.push({text:e,type:o.ok?"success":"danger"}))),(0,r.g)(t,n,5e3),o.ok){t.querySelectorAll(".bigup__form_input").forEach((e=>{e.value=""}));const e=t.querySelector(".bigup__customFileUpload_output");e&&(0,l.e)(e)}}catch(e){console.error(e)}finally{n.fF&&console.log((0,n.P_)()+" | END | handleSubmit")}}},667:(e,t,o)=>{o.d(t,{c:()=>i,e:()=>a});var n=o(907);function a(e){return n.fF&&console.log(`${(0,n.P_)()} |START| removeChildren | ${e.classList}`),new Promise(((t,o)=>{try{for(;e.firstChild;)e.removeChild(e.firstChild);t("Child nodes removed successfully.")}catch(e){o(e)}finally{n.fF&&console.log(`${(0,n.P_)()} | END | removeChildren | ${e.classList}`)}}))}function i(e){return"string"!=typeof e?(console.error(`makeHumanReadable expects a string, but ${typeof e} received.`,e),"error getting message"):e.replace(/(?<!\([^)]*?)<[^>]*?>/g,"").match(/(\([^\)]*?\))|[ \p{L}\p{N}\p{M}\p{P}]/gu).join("").replace(/^\s*|\s(?=\s)|\s*$/g,"")}}},t={};function o(n){var a=t[n];if(void 0!==a)return a.exports;var i=t[n]={exports:{}};return e[n](i,i.exports,o),i.exports}o.d=(e,t)=>{for(var n in t)o.o(t,n)&&!o.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},o.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{var e=o(870),t=o(523),n=o(466);let a=setInterval((()=>{"complete"===document.readyState&&(clearInterval(a),document.querySelectorAll(".saveTheBees").forEach((e=>{"none"!==e.style.display&&(e.style.display="none")})),document.querySelectorAll(".bigup__form").forEach((o=>{o.addEventListener("submit",e.P),(0,t.w)(o,!1);const a=o.querySelector(".bigup__customFileUpload_input");a&&a.addEventListener("change",n.W)})))}),250)})()})();
//# sourceMappingURL=bigup-contact-form-public.js.map