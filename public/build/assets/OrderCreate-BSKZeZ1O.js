import{Z as X,F as T}from"./@inertiajs-D0DMCEQB.js";import{_ as Z}from"./SectionMain-Vot1TOkK.js";import{L as Q}from"./LayoutAuthenticated-CnzsnGDD.js";import{r as V,c as D,e as E,b2 as q,aU as l,a0 as W,bH as B,aa as H,a3 as r,bM as Y,bJ as g,bA as S,a2 as c,F as k,b0 as f,bf as m,a9 as I,a1 as N,bz as P}from"./@vue-BRgAy5zV.js";import{a as v}from"./axios-CCb-kr4I.js";import{l as $}from"./lodash-D4AGecfG.js";import{u as ee}from"./vue-toastification-ejyjJRED.js";import{_ as re}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./deepmerge-CtOfIP5S.js";import"./call-bind-aBC2DkHY.js";import"./get-intrinsic-BKEvijrG.js";import"./es-errors-DzOT6E3C.js";import"./has-symbols-eVqrYdw7.js";import"./has-proto-JnoBQRdH.js";import"./function-bind-BbkWVFrm.js";import"./hasown-DYqjtFKE.js";import"./set-function-length-B5OANX0j.js";import"./define-data-property-DO9o5wXF.js";import"./es-define-property-tzmkNPou.js";import"./gopd-CEkvUycD.js";import"./has-property-descriptors-DphFXkuo.js";import"./qs-Bnt7aRCy.js";import"./side-channel-FJR906QQ.js";import"./object-inspect-D3SFxae_.js";import"./nprogress-uqLJ5xmn.js";import"./lodash.clonedeep-Bxvn-V0B.js";import"./lodash.isequal-BYpQg7Um.js";import"./@mdi-DWfqH7fj.js";import"./app-YDObI9lJ.js";import"./jspdf-Ciebq7We.js";import"./@babel-BsCcpEdk.js";import"./fflate-EnPhvkmS.js";import"./laravel-echo-C9ppFO4I.js";import"./pusher-js-Cl-1XqxI.js";import"./laravel-vite-plugin-DEL3ZhID.js";import"./pinia-BhnhDyRZ.js";import"./firebase-DL3QX-oU.js";import"./@firebase--zA4YiqN.js";import"./idb-Bn1DMRyg.js";import"./BaseIcon-B4kwkOCn.js";import"./colors-S7_1wET5.js";import"./OverlayLayer-BSs7OF78.js";const te={components:{Head:X,SectionMain:Z,LayoutAuthenticated:Q},props:{vouchers:Array,paymentMethods:Array},setup(x){const e=ee(),d=V({user_id:"",voucher_id:null,payment_method_id:"",order_items:[],note:"",total_amount:0,discount_amount:0}),a=V(""),p=V([]),R=V(null),L=$.debounce(async()=>{if(a.value.length<2){p.value=[];return}try{const t=await v.get("/api/users/search",{params:{query:a.value}});p.value=t.data.data}catch(t){console.error("Search error:",t),p.value=[]}},300),F=t=>{d.value.user_id=t.id,R.value=t,a.value=t.full_name,p.value=[]},M=()=>{d.value.order_items.push({item_type:"product",item_id:null,service_type:"single",quantity:1,price:0,search:"",searchResults:[],selectedItem:null})},o=t=>{d.value.order_items.splice(t,1)},b=async t=>{const s=d.value.order_items[t];if(s.search.length<2){s.searchResults=[];return}try{const n=s.item_type==="product"?"/api/products/search":"/api/services/search",u=await v.get(n,{params:{query:s.search}});s.searchResults=u.data.data.map(h=>({...h,item_type:s.item_type}))}catch(n){console.error("Error searching items:",n),s.searchResults=[]}},i=(t,s)=>{const n=d.value.order_items[t];n.selectedItem=s,n.item_id=s.id,n.search=s.name||s.service_name,s.item_type==="service"?C(n):n.price=Number(s.price),n.searchResults=[],w()},y=()=>d.value.order_items.reduce((t,s)=>t+s.quantity*s.price,0),_=()=>{if(!d.value.voucher_id)return 0;const t=x.vouchers.find(n=>n.id===d.value.voucher_id);if(!t)return 0;const s=y();return t.discount_type==="percentage"?s*(t.discount_value/100):t.discount_value},A=()=>Math.max(0,y()-_()),w=()=>{d.value.total_amount=A(),d.value.discount_amount=_()},G=async()=>{var t,s,n;try{if(console.log("Bắt đầu submit form với dữ liệu:",d.value),!d.value.user_id){e.error("Vui lòng chọn khách hàng");return}if(d.value.order_items.length===0){e.error("Vui lòng thêm ít nhất một sản phẩm hoặc dịch vụ");return}d.value.total_amount=y(),d.value.discount_amount=_(),console.log("Tổng tiền đã tính:",{total:d.value.total_amount,discount:d.value.discount_amount}),console.log("Gọi API tạo đơn hàng");const u=await v.post("/api/orders",d.value);console.log("Response tạo đơn hàng:",u.data);const h=u.data.data;d.value.payment_method_id===3?(console.log("Phương thức thanh toán PayOS được chọn"),await O(h)):(console.log("Chuyển hướng đến trang chi tiết đơn hàng"),T.visit(`/orders/${h.id}`))}catch(u){console.error("Chi tiết lỗi tạo đơn hàng:",{message:u.message,response:(t=u.response)==null?void 0:t.data,stack:u.stack}),e.error(((n=(s=u.response)==null?void 0:s.data)==null?void 0:n.message)||"Có lỗi xảy ra khi tạo đơn hàng")}},K=t=>new Intl.NumberFormat("vi-VN",{style:"currency",currency:"VND"}).format(t),z=D(()=>["pending","confirmed","shipping","delivered"].includes(x.order.status));E(()=>d.value.order_items,t=>{t.forEach(s=>{s.selectedItem&&s.item_type==="service"&&C(s)})},{deep:!0});const C=t=>{if(t.selectedItem){switch(t.service_type){case"combo_5":t.price=Number(t.selectedItem.combo_5_price||0);break;case"combo_10":t.price=Number(t.selectedItem.combo_10_price||0);break;default:t.price=Number(t.selectedItem.single_price||0);break}w()}};E(()=>d.value.order_items.map(t=>t.service_type),()=>{d.value.order_items.forEach(t=>{t.item_type==="service"&&t.selectedItem&&C(t)})},{deep:!0});const O=async t=>{var s,n;try{const u=`${window.location.origin}/payment/callback`,h=`${window.location.origin}/payment/callback?status=cancel`,U=await v.post(`/api/orders/${t.id}/payment-link`,{returnUrl:u,cancelUrl:h});if(U.data.success&&U.data.data.checkoutUrl)window.location.href=U.data.data.checkoutUrl;else throw new Error(U.data.message||"Không thể tạo link thanh toán")}catch(u){e.error("Lỗi xử lý thanh toán: "+(((n=(s=u.response)==null?void 0:s.data)==null?void 0:n.message)||u.message)),T.visit(`/orders/${t.id}`)}},j=async()=>{const s=new URLSearchParams(window.location.search).get("orderCode");if(!s){e.error("Thiếu thông tin đơn hàng");return}try{const n=await v.post("/api/payment/verify",{orderCode:s});n.data.success?(e.success("Thanh toán thành công"),T.visit(`/orders/${n.data.data.order_id}`)):(e.error("Thanh toán thất bại"),T.visit("/orders"))}catch(n){console.error("Payment verification error:",n),e.error("Lỗi xác thực thanh toán")}},J=D(()=>d.value.total_amount-d.value.discount_amount);return{form:d,userSearch:a,userResults:p,selectedUser:R,searchUsers:L,selectUser:F,addOrderItem:M,removeOrderItem:o,searchItems:b,selectItem:i,calculateTotal:y,calculateDiscount:_,calculateFinalTotal:A,formatCurrency:K,submitForm:G,canUpdateStatus:z,updateServicePrice:C,handlePayOSPayment:O,handlePaymentCallback:j,finalAmount:J,applyVoucher:async t=>{try{if(d.value.voucher_id=t,t){const s=x.vouchers.find(n=>n.id===t);s&&(s.discount_type==="percentage"?d.value.discount_amount=d.value.total_amount*s.discount_value/100:d.value.discount_amount=s.discount_value)}else d.value.discount_amount=0;w()}catch(s){e.error("Lỗi khi áp dụng voucher: "+s.message)}},updateTotals:w}}},oe={class:"container mx-auto px-4 py-8"},ae={class:"mb-4"},se={class:"relative"},de={key:0,class:"absolute z-50 w-full mt-1 bg-white dark:bg-dark-surface rounded-md shadow-lg border border-gray-200 dark:border-dark-border"},ne=["onClick"],ie={class:"font-medium text-gray-900 dark:text-dark-text"},le={class:"text-sm text-gray-500 dark:text-dark-text-muted"},ce={key:0,class:"ml-2"},ue={class:"grid grid-cols-2 gap-4"},me=["onUpdate:modelValue","onChange"],be={key:0},ge=["onUpdate:modelValue"],pe={class:"mt-4"},he=["onUpdate:modelValue","onInput","placeholder"],ye={key:0,class:"mt-1 bg-white dark:bg-dark-surface border border-gray-200 dark:border-dark-border shadow-lg max-h-60 rounded-md py-1 text-base overflow-auto focus:outline-none sm:text-sm"},ke=["onClick"],fe={class:"flex flex-col"},ve={class:"font-medium text-gray-900 dark:text-dark-text"},xe={key:0,class:"text-sm text-gray-600 dark:text-dark-text-muted"},_e={key:1,class:"text-sm text-gray-600 dark:text-dark-text-muted"},we={class:"mt-4 grid grid-cols-3 gap-4"},Ce=["onUpdate:modelValue"],Ue=["value"],Te=["value"],Ve=["onClick"],Se=["value"],Ie=["value"],Ne={class:"flex justify-between items-center"},Pe={class:"text-right"},Re={class:"text-sm text-gray-600 dark:text-gray-400"},Le={class:"text-sm text-red-600 dark:text-red-400"},Fe={class:"text-lg font-bold text-indigo-600 dark:text-indigo-400"};function Me(x,e,d,a,p,R){const L=q("Head"),F=q("SectionMain"),M=q("LayoutAuthenticated");return l(),W(M,null,{default:B(()=>[H(L,{title:"Tạo đơn hàng mới"}),H(F,null,{default:B(()=>[r("div",oe,[e[24]||(e[24]=r("h1",{class:"text-3xl font-bold mb-6 text-gray-900 dark:text-dark-text"},"Tạo đơn hàng mới",-1)),r("form",{onSubmit:e[6]||(e[6]=Y((...o)=>a.submitForm&&a.submitForm(...o),["prevent"])),class:"space-y-6"},[r("div",ae,[e[7]||(e[7]=r("label",{class:"block text-sm font-medium text-gray-700 dark:text-dark-text-secondary mb-1"}," Khách hàng ",-1)),r("div",se,[g(r("input",{"onUpdate:modelValue":e[0]||(e[0]=o=>a.userSearch=o),onInput:e[1]||(e[1]=(...o)=>a.searchUsers&&a.searchUsers(...o)),type:"text",placeholder:"Nhập tên hoặc số điện thoại khách hàng...",class:"mt-1 block w-full rounded-md border-gray-300 dark:border-dark-border bg-white dark:bg-dark-surface text-gray-900 dark:text-dark-text shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"},null,544),[[S,a.userSearch]]),a.userResults.length>0?(l(),c("div",de,[(l(!0),c(k,null,f(a.userResults,o=>(l(),c("div",{key:o.id,onClick:b=>a.selectUser(o),class:"p-3 hover:bg-gray-50 dark:hover:bg-dark-surface-hover cursor-pointer border-b border-gray-100 dark:border-dark-border last:border-0"},[r("div",ie,m(o.full_name),1),r("div",le,[I(" SĐT: "+m(o.phone_number)+" ",1),o.email?(l(),c("span",ce,"Email: "+m(o.email),1)):N("",!0)])],8,ne))),128))])):N("",!0)])]),r("div",null,[e[18]||(e[18]=r("h3",{class:"text-lg font-medium text-gray-900 dark:text-dark-text mb-2"},"Sản phẩm/Dịch vụ",-1)),r("button",{type:"button",onClick:e[2]||(e[2]=(...o)=>a.addOrderItem&&a.addOrderItem(...o)),class:"mb-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-dark-bg"}," Thêm sản phẩm/dịch vụ "),(l(!0),c(k,null,f(a.form.order_items,(o,b)=>(l(),c("div",{key:b,class:"mb-4 p-4 border rounded-md border-gray-200 dark:border-dark-border bg-white dark:bg-dark-surface"},[r("div",ue,[r("div",null,[e[9]||(e[9]=r("label",{class:"block text-sm font-medium text-gray-700 dark:text-gray-300"},"Loại:",-1)),g(r("select",{"onUpdate:modelValue":i=>o.item_type=i,onChange:i=>a.searchItems(b),class:"mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-dark-surface dark:border-dark-border dark:text-gray-300"},e[8]||(e[8]=[r("option",{value:"product"},"Sản phẩm",-1),r("option",{value:"service"},"Dịch vụ",-1)]),40,me),[[P,o.item_type]])]),o.item_type==="service"?(l(),c("div",be,[e[11]||(e[11]=r("label",{class:"block text-sm font-medium text-gray-700 dark:text-gray-300"},"Loại dịch vụ:",-1)),g(r("select",{"onUpdate:modelValue":i=>o.service_type=i,class:"mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-dark-surface dark:border-dark-border dark:text-gray-300"},e[10]||(e[10]=[r("option",{value:"single"},"Đơn lẻ",-1),r("option",{value:"combo_5"},"Combo 5",-1),r("option",{value:"combo_10"},"Combo 10",-1)]),8,ge),[[P,o.service_type]])])):N("",!0)]),r("div",pe,[e[14]||(e[14]=r("label",{class:"block text-sm font-medium text-gray-700 dark:text-gray-300"},"Tìm kiếm:",-1)),g(r("input",{type:"text","onUpdate:modelValue":i=>o.search=i,onInput:i=>a.searchItems(b),class:"mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-dark-surface dark:border-dark-border dark:text-gray-300",placeholder:o.item_type==="product"?"Tìm sản phẩm":"Tìm liệu trình"},null,40,he),[[S,o.search]]),o.searchResults.length>0?(l(),c("ul",ye,[(l(!0),c(k,null,f(o.searchResults,i=>(l(),c("li",{key:i.id,onClick:y=>a.selectItem(b,i),class:"cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-primary-50 dark:hover:bg-dark-surface-hover"},[r("div",fe,[r("span",ve,m(i.name||i.service_name),1),i.item_type==="service"?(l(),c("span",xe,[I(" Đơn lẻ: "+m(a.formatCurrency(i.single_price))+" ",1),e[12]||(e[12]=r("br",null,null,-1)),I(" Combo 5: "+m(a.formatCurrency(i.combo_5_price))+" ",1),e[13]||(e[13]=r("br",null,null,-1)),I(" Combo 10: "+m(a.formatCurrency(i.combo_10_price)),1)])):(l(),c("span",_e,m(a.formatCurrency(i.price)),1))])],8,ke))),128))])):N("",!0)]),r("div",we,[r("div",null,[e[15]||(e[15]=r("label",{class:"block text-sm font-medium text-gray-700 dark:text-dark-text-secondary"},"Số lượng:",-1)),g(r("input",{"onUpdate:modelValue":i=>o.quantity=i,type:"number",min:"1",class:"mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-dark-border rounded-md dark:bg-dark-surface dark:text-dark-text"},null,8,Ce),[[S,o.quantity,void 0,{number:!0}]])]),r("div",null,[e[16]||(e[16]=r("label",{class:"block text-sm font-medium text-gray-700 dark:text-dark-text-secondary"},"Đơn giá:",-1)),r("input",{value:a.formatCurrency(o.price),type:"text",class:"mt-1 block w-full rounded-md border-gray-300 dark:border-dark-border shadow-sm sm:text-sm bg-white dark:bg-dark-surface disabled:bg-gray-50 dark:disabled:bg-dark-surface disabled:text-gray-700 dark:disabled:text-dark-text disabled:border-gray-300 dark:disabled:border-dark-border disabled:cursor-not-allowed",readonly:""},null,8,Ue)]),r("div",null,[e[17]||(e[17]=r("label",{class:"block text-sm font-medium text-gray-700 dark:text-dark-text-secondary"},"Thành tiền:",-1)),r("input",{value:a.formatCurrency(o.quantity*o.price),class:"mt-1 block w-full rounded-md border-gray-300 dark:border-dark-border shadow-sm sm:text-sm bg-white dark:bg-dark-surface disabled:bg-gray-50 dark:disabled:bg-dark-surface disabled:text-gray-700 dark:disabled:text-dark-text disabled:border-gray-300 dark:disabled:border-dark-border disabled:cursor-not-allowed",readonly:""},null,8,Te)])]),r("button",{type:"button",onClick:i=>a.removeOrderItem(b),class:"mt-2 inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"}," Xóa ",8,Ve)]))),128))]),r("div",null,[e[20]||(e[20]=r("label",{class:"block text-sm font-medium text-gray-700 dark:text-gray-300"},"Voucher:",-1)),g(r("select",{"onUpdate:modelValue":e[3]||(e[3]=o=>a.form.voucher_id=o),class:"mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-dark-surface dark:border-dark-border dark:text-gray-300"},[e[19]||(e[19]=r("option",{value:""},"Không áp dụng",-1)),(l(!0),c(k,null,f(d.vouchers,o=>(l(),c("option",{key:o.id,value:o.id},m(o.code)+" - "+m(o.discount_type==="percentage"?`${o.discount_value}%`:a.formatCurrency(o.discount_value)),9,Se))),128))],512),[[P,a.form.voucher_id]])]),r("div",null,[e[21]||(e[21]=r("label",{class:"block text-sm font-medium text-gray-700 dark:text-gray-300"},"Phương thức thanh toán:",-1)),g(r("select",{"onUpdate:modelValue":e[4]||(e[4]=o=>a.form.payment_method_id=o),required:"",class:"mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-dark-surface dark:border-dark-border dark:text-gray-300"},[(l(!0),c(k,null,f(d.paymentMethods,o=>(l(),c("option",{key:o.id,value:o.id},m(o.method_name),9,Ie))),128))],512),[[P,a.form.payment_method_id]])]),r("div",null,[e[22]||(e[22]=r("label",{class:"block text-sm font-medium text-gray-700 dark:text-gray-300"},"Ghi chú:",-1)),g(r("textarea",{"onUpdate:modelValue":e[5]||(e[5]=o=>a.form.note=o),rows:"3",class:"mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-dark-surface dark:border-dark-border dark:text-gray-300"},null,512),[[S,a.form.note]])]),r("div",Ne,[e[23]||(e[23]=r("button",{type:"submit",class:"inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"}," Tạo đơn hàng và hóa đơn ",-1)),r("div",Pe,[r("p",Re,"Tổng tiền: "+m(a.formatCurrency(a.calculateTotal())),1),r("p",Le,"Giảm giá: -"+m(a.formatCurrency(a.calculateDiscount())),1),r("p",Fe,"Thành tiền: "+m(a.formatCurrency(a.calculateFinalTotal())),1)])])],32)])]),_:1})]),_:1})}const Ur=re(te,[["render",Me],["__scopeId","data-v-fca9e452"]]);export{Ur as default};