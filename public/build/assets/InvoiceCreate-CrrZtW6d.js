import{Z as j,F as D}from"./@inertiajs-D0DMCEQB.js";import{_ as K}from"./SectionMain-BbIPmLtw.js";import{L as P}from"./LayoutAuthenticated-D71Hf2y4.js";import{r as T,e as E,c as z,b2 as M,aU as l,a0 as G,bH as L,aa as A,a3 as e,bM as X,bJ as y,bA as S,a2 as u,F as f,b0 as x,bf as c,a9 as U,a1 as h,bz as V}from"./@vue-BRgAy5zV.js";import{a as I}from"./axios-CCb-kr4I.js";import{l as J}from"./lodash-D4AGecfG.js";import{u as Z}from"./vue-toastification-ejyjJRED.js";import{_ as Q}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./deepmerge-CtOfIP5S.js";import"./call-bind-aBC2DkHY.js";import"./get-intrinsic-BKEvijrG.js";import"./es-errors-DzOT6E3C.js";import"./has-symbols-eVqrYdw7.js";import"./has-proto-JnoBQRdH.js";import"./function-bind-BbkWVFrm.js";import"./hasown-DYqjtFKE.js";import"./set-function-length-B5OANX0j.js";import"./define-data-property-DO9o5wXF.js";import"./es-define-property-tzmkNPou.js";import"./gopd-CEkvUycD.js";import"./has-property-descriptors-DphFXkuo.js";import"./qs-Bnt7aRCy.js";import"./side-channel-FJR906QQ.js";import"./object-inspect-D3SFxae_.js";import"./nprogress-uqLJ5xmn.js";import"./lodash.clonedeep-Bxvn-V0B.js";import"./lodash.isequal-BYpQg7Um.js";import"./@mdi-Dzgtc9aE.js";import"./app-DT9Ddco3.js";import"./jspdf-Ciebq7We.js";import"./@babel-BsCcpEdk.js";import"./fflate-EnPhvkmS.js";import"./laravel-echo-C9ppFO4I.js";import"./pusher-js-Cl-1XqxI.js";import"./laravel-vite-plugin-DEL3ZhID.js";import"./pinia-BhnhDyRZ.js";import"./firebase-DL3QX-oU.js";import"./@firebase--zA4YiqN.js";import"./idb-Bn1DMRyg.js";import"./BaseIcon-B4kwkOCn.js";import"./colors-S7_1wET5.js";import"./OverlayLayer-BSs7OF78.js";const W={components:{Head:j,SectionMain:K,LayoutAuthenticated:P},props:{vouchers:Array,paymentMethods:Array},setup(k){const t=Z(),n=T({user_id:"",voucher_id:null,payment_method_id:"",order_items:[],note:"",total_amount:0,discount_amount:0}),s=T(""),b=T([]),v=T(null),N=J.debounce(async()=>{if(s.value.length<2){b.value=[];return}try{const a=await I.get("/api/users/search",{params:{query:s.value}});b.value=a.data.data||[]}catch(a){console.error("Search error:",a),b.value=[]}},300),R=a=>{n.value.user_id=a.id,v.value=a,s.value=a.full_name,b.value=[]},q=()=>{n.value.order_items.push({item_type:"product",item_id:null,service_type:"single",quantity:1,price:0,search:"",searchResults:[],selectedItem:null})},_=a=>{n.value.order_items.splice(a,1)},w=async a=>{const r=n.value.order_items[a];if(r.search.length<2){r.searchResults=[];return}try{const d=r.item_type==="product"?"/api/products/search":"/api/services/search",p=await I.get(d,{params:{query:r.search}});r.searchResults=p.data.data.map(g=>({...g,item_type:r.item_type}))}catch(d){console.error("Error searching items:",d),r.searchResults=[]}},o=(a,r)=>{const d=n.value.order_items[a];if(d.selectedItem=r,d.item_id=r.id,d.search=r.name||r.service_name,r.item_type==="service")if(d.service_type||(d.service_type="single"),r.single_price!==void 0)switch(d.service_type){case"combo_5":d.price=Number(r.combo_5_price);break;case"combo_10":d.price=Number(r.combo_10_price);break;default:d.price=Number(r.single_price);break}else d.price=Number(r.price);else d.price=Number(r.price);d.searchResults=[],m()};E(()=>n.value.order_items,a=>{a.forEach(r=>{if(r.selectedItem&&r.item_type==="service")if(r.selectedItem.single_price!==void 0)switch(r.service_type){case"combo_5":r.price=Number(r.selectedItem.combo_5_price);break;case"combo_10":r.price=Number(r.selectedItem.combo_10_price);break;default:r.price=Number(r.selectedItem.single_price);break}else r.price=Number(r.selectedItem.price)}),m()},{deep:!0}),E(()=>n.value.order_items,a=>{a.forEach((r,d)=>{const p=r._prevItemType;p&&p!==r.item_type&&(r.item_id=null,r.price=0,r.search="",r.searchResults=[],r.selectedItem=null,r.service_type=r.item_type==="service"?"single":null),r._prevItemType=r.item_type})},{deep:!0});const m=()=>{const a=n.value.order_items.reduce((r,d)=>r+d.quantity*d.price,0);if(n.value.total_amount=a,n.value.voucher_id){const r=k.vouchers.find(d=>d.id===n.value.voucher_id);r&&(n.value.discount_amount=r.discount_type==="percentage"?a*r.discount_value/100:r.discount_value)}n.value.final_total=Math.max(0,a-(n.value.discount_amount||0))},i=()=>n.value.order_items.reduce((a,r)=>{const d=(r.quantity||0)*(r.price||0);return a+d},0),C=()=>{if(!n.value.voucher_id)return 0;const a=k.vouchers.find(d=>d.id===n.value.voucher_id);if(!a)return 0;const r=i();return a.discount_type==="percentage"?r*(a.discount_value/100):a.discount_value},F=()=>{const a=i(),r=C();return Math.max(0,a-r)},O=a=>new Intl.NumberFormat("vi-VN",{style:"currency",currency:"VND"}).format(a),H=async()=>{var a,r;try{n.value.total_amount=F(),n.value.discount_amount=C();const d=await I.post("/api/invoices",n.value),p=k.paymentMethods.find(g=>g.id===n.value.payment_method_id);if(p!=null&&p.method_name.toLowerCase().includes("chuyển khoản"))try{const g=await I.post(`/api/invoices/${d.data.data.id}/pay-with-payos`);if(g.data.success&&g.data.checkoutUrl){D.visit(g.data.checkoutUrl,{method:"get",preserveState:!1});return}}catch(g){console.error("PayOS payment error:",g),t.error("Không thể khởi tạo thanh toán PayOS");return}D.visit(`/invoices/${d.data.data.id}`,{method:"get",preserveState:!1}),t.success("Tạo hóa đơn thành công!")}catch(d){console.error("Error creating invoice:",d),t.error(((r=(a=d.response)==null?void 0:a.data)==null?void 0:r.message)||"Có lỗi xảy ra khi tạo hóa đơn!")}},B=z(()=>v.value);return{form:n,userSearch:s,userResults:b,selectedUser:v,searchUsers:N,selectUser:R,addOrderItem:q,removeOrderItem:_,searchItems:w,selectItem:o,calculateTotal:i,calculateDiscount:C,calculateFinalTotal:F,formatCurrency:O,submitForm:H,selectedCustomer:B}}},Y={class:"container mx-auto px-4 py-8"},$={class:"mb-4"},ee={class:"relative"},te={key:0,class:"absolute z-50 w-full mt-1 bg-white dark:bg-dark-surface rounded-md shadow-lg border border-gray-200 dark:border-dark-border max-h-60 overflow-y-auto"},re=["onClick"],oe={class:"font-medium dark:text-gray-300"},se={class:"text-sm text-gray-500 dark:text-gray-400"},ae={key:0,class:"ml-2"},de={class:"grid grid-cols-2 gap-4"},ne=["onUpdate:modelValue","onChange"],ie={key:0},le=["onUpdate:modelValue"],ce={class:"mt-4"},ue=["onUpdate:modelValue","onInput","placeholder"],me={key:0,class:"mt-1 bg-white dark:bg-dark-surface shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 dark:ring-gray-700 overflow-auto focus:outline-none sm:text-sm"},ge=["onClick"],pe={class:"flex flex-col"},ye={class:"font-medium dark:text-gray-300"},be={key:0,class:"text-sm dark:text-gray-400"},fe={key:1,class:"text-sm dark:text-gray-400"},xe={class:"mt-4 grid grid-cols-3 gap-4"},he=["onUpdate:modelValue"],ke=["value"],ve=["value"],_e=["onClick"],we=["value"],Ce=["value"],Te={class:"mt-8"},Se={key:0,class:"bg-white dark:bg-dark-surface shadow-lg rounded-lg overflow-hidden"},Ue={class:"p-6 space-y-6"},Ve={class:"bg-gray-50 dark:bg-dark-surface/50 rounded-lg p-4"},Ie={class:"grid grid-cols-2 gap-4"},Ne={class:"font-medium dark:text-gray-300"},Re={class:"font-medium dark:text-gray-300"},qe={class:"border rounded-lg overflow-hidden"},Me={class:"min-w-full divide-y divide-gray-200 dark:divide-dark-border"},Fe={class:"bg-white dark:bg-dark-surface divide-y divide-gray-200 dark:divide-dark-border"},De={class:"px-6 py-4"},Ee={class:"text-sm text-gray-900 dark:text-gray-100"},Le={key:0,class:"text-xs text-gray-500 dark:text-gray-400"},Ae={class:"px-6 py-4 text-right text-sm text-gray-500 dark:text-gray-400"},Oe={class:"px-6 py-4 text-right text-sm text-gray-500 dark:text-gray-400"},He={class:"px-6 py-4 text-right text-sm text-gray-900 font-medium dark:text-gray-100"},Be={class:"bg-gray-50 dark:bg-dark-surface/50 rounded-lg p-4 space-y-2"},je={class:"flex justify-between"},Ke={class:"font-medium dark:text-gray-300"},Pe={class:"flex justify-between"},ze={class:"font-medium text-red-600 dark:text-red-400"},Ge={class:"flex justify-between text-lg font-bold pt-2 border-t"},Xe={class:"text-indigo-600 dark:text-indigo-400"};function Je(k,t,n,s,b,v){const N=M("Head"),R=M("SectionMain"),q=M("LayoutAuthenticated");return l(),G(q,null,{default:L(()=>[A(N,{title:"Tạo hóa đơn mới"}),A(R,null,{default:L(()=>{var _,w;return[e("div",Y,[t[33]||(t[33]=e("h1",{class:"text-3xl font-bold mb-6 dark:text-gray-100"},"Tạo hóa đơn mới",-1)),e("form",{onSubmit:t[6]||(t[6]=X((...o)=>s.submitForm&&s.submitForm(...o),["prevent"])),class:"space-y-6"},[e("div",$,[t[7]||(t[7]=e("label",{class:"block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"}," Khách hàng ",-1)),e("div",ee,[y(e("input",{"onUpdate:modelValue":t[0]||(t[0]=o=>s.userSearch=o),onInput:t[1]||(t[1]=(...o)=>s.searchUsers&&s.searchUsers(...o)),type:"text",placeholder:"Nhập tên hoặc số điện thoại khách hàng...",class:"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-dark-surface dark:border-dark-border dark:text-gray-300"},null,544),[[S,s.userSearch]]),s.userResults.length>0?(l(),u("div",te,[(l(!0),u(f,null,x(s.userResults,o=>(l(),u("div",{key:o.id,onClick:m=>s.selectUser(o),class:"p-3 hover:bg-gray-50 dark:hover:bg-dark-surface/70 cursor-pointer border-b border-gray-100 dark:border-dark-border last:border-0"},[e("div",oe,c(o.full_name),1),e("div",se,[U(" SĐT: "+c(o.phone_number)+" ",1),o.email?(l(),u("span",ae,"Email: "+c(o.email),1)):h("",!0)])],8,re))),128))])):h("",!0)])]),e("div",null,[t[18]||(t[18]=e("h3",{class:"text-lg font-medium text-gray-900 dark:text-gray-100 mb-2"},"Sản phẩm/Dịch vụ",-1)),e("button",{type:"button",onClick:t[2]||(t[2]=(...o)=>s.addOrderItem&&s.addOrderItem(...o)),class:"mb-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"}," Thêm sản phẩm/dịch vụ "),(l(!0),u(f,null,x(s.form.order_items,(o,m)=>(l(),u("div",{key:m,class:"mb-4 p-4 border rounded-md dark:border-dark-border dark:bg-dark-surface"},[e("div",de,[e("div",null,[t[9]||(t[9]=e("label",{class:"block text-sm font-medium text-gray-700 dark:text-gray-300"},"Loại:",-1)),y(e("select",{"onUpdate:modelValue":i=>o.item_type=i,onChange:i=>s.searchItems(m),class:"mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-dark-surface dark:border-dark-border dark:text-gray-300"},t[8]||(t[8]=[e("option",{value:"product"},"Sản phẩm",-1),e("option",{value:"service"},"Dịch vụ",-1)]),40,ne),[[V,o.item_type]])]),o.item_type==="service"?(l(),u("div",ie,[t[11]||(t[11]=e("label",{class:"block text-sm font-medium text-gray-700 dark:text-gray-300"},"Loại dịch vụ:",-1)),y(e("select",{"onUpdate:modelValue":i=>o.service_type=i,class:"mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-dark-surface dark:border-dark-border dark:text-gray-300"},t[10]||(t[10]=[e("option",{value:"single"},"Đơn lẻ",-1),e("option",{value:"combo_5"},"Combo 5",-1),e("option",{value:"combo_10"},"Combo 10",-1)]),8,le),[[V,o.service_type]])])):h("",!0)]),e("div",ce,[t[14]||(t[14]=e("label",{class:"block text-sm font-medium text-gray-700 dark:text-gray-300"},"Tìm kiếm:",-1)),y(e("input",{type:"text","onUpdate:modelValue":i=>o.search=i,onInput:i=>s.searchItems(m),class:"mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-dark-surface dark:border-dark-border dark:text-gray-300",placeholder:o.item_type==="product"?"Tìm sản phẩm":"Tìm liệu trình"},null,40,ue),[[S,o.search]]),o.searchResults.length>0?(l(),u("ul",me,[(l(!0),u(f,null,x(o.searchResults,i=>(l(),u("li",{key:i.id,onClick:C=>s.selectItem(m,i),class:"cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600/80"},[e("div",pe,[e("span",ye,c(i.name||i.service_name),1),i.item_type==="service"?(l(),u("span",be,[U(" Đơn lẻ: "+c(s.formatCurrency(i.single_price))+" ",1),t[12]||(t[12]=e("br",null,null,-1)),U(" Combo 5: "+c(s.formatCurrency(i.combo_5_price))+" ",1),t[13]||(t[13]=e("br",null,null,-1)),U(" Combo 10: "+c(s.formatCurrency(i.combo_10_price)),1)])):(l(),u("span",fe,c(s.formatCurrency(i.price)),1))])],8,ge))),128))])):h("",!0)]),e("div",xe,[e("div",null,[t[15]||(t[15]=e("label",{class:"block text-sm font-medium text-gray-700 dark:text-gray-300"},"Số lượng:",-1)),y(e("input",{"onUpdate:modelValue":i=>o.quantity=i,type:"number",min:"1",class:"mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-dark-surface dark:border-dark-border dark:text-gray-300"},null,8,he),[[S,o.quantity,void 0,{number:!0}]])]),e("div",null,[t[16]||(t[16]=e("label",{class:"block text-sm font-medium text-gray-700 dark:text-gray-300"},"Đơn giá:",-1)),e("input",{value:s.formatCurrency(o.price),type:"text",class:"mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm dark:bg-dark-surface dark:border-dark-border dark:text-gray-300",readonly:"",disabled:!0},null,8,ke)]),e("div",null,[t[17]||(t[17]=e("label",{class:"block text-sm font-medium text-gray-700 dark:text-gray-300"},"Thành tiền:",-1)),e("input",{value:s.formatCurrency(o.quantity*o.price),readonly:"",class:"mt-1 bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-dark-surface dark:border-dark-border dark:text-gray-300"},null,8,ve)])]),e("button",{type:"button",onClick:i=>s.removeOrderItem(m),class:"mt-2 inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"}," Xóa ",8,_e)]))),128))]),e("div",null,[t[20]||(t[20]=e("label",{for:"voucher",class:"block text-sm font-medium text-gray-700 dark:text-gray-300"},"Voucher:",-1)),y(e("select",{"onUpdate:modelValue":t[3]||(t[3]=o=>s.form.voucher_id=o),class:"mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-dark-surface dark:border-dark-border dark:text-gray-300"},[t[19]||(t[19]=e("option",{value:""},"Không áp dụng",-1)),(l(!0),u(f,null,x(n.vouchers,o=>(l(),u("option",{key:o.id,value:o.id},c(o.code)+" - "+c(o.discount_type==="percentage"?`${o.discount_value}%`:s.formatCurrency(o.discount_value)),9,we))),128))],512),[[V,s.form.voucher_id]])]),e("div",null,[t[21]||(t[21]=e("label",{for:"payment_method",class:"block text-sm font-medium text-gray-700 dark:text-gray-300"},"Phương thức thanh toán:",-1)),y(e("select",{"onUpdate:modelValue":t[4]||(t[4]=o=>s.form.payment_method_id=o),required:"",class:"mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-dark-surface dark:border-dark-border dark:text-gray-300"},[(l(!0),u(f,null,x(n.paymentMethods,o=>(l(),u("option",{key:o.id,value:o.id},c(o.method_name),9,Ce))),128))],512),[[V,s.form.payment_method_id]])]),e("div",null,[t[22]||(t[22]=e("label",{for:"note",class:"block text-sm font-medium text-gray-700 dark:text-gray-300"},"Ghi chú:",-1)),y(e("textarea",{"onUpdate:modelValue":t[5]||(t[5]=o=>s.form.note=o),rows:"3",class:"mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-dark-surface dark:border-dark-border dark:text-gray-300"},null,512),[[S,s.form.note]])]),t[23]||(t[23]=e("button",{type:"submit",class:"inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"}," Tạo hóa đơn ",-1))],32),e("div",Te,[t[32]||(t[32]=e("h2",{class:"text-2xl font-bold mb-4 dark:text-gray-100"},"Xem trước hóa đơn",-1)),s.form.user_id?(l(),u("div",Se,[t[31]||(t[31]=e("div",{class:"bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4"},[e("h3",{class:"text-xl text-white font-semibold"},"Thông tin hóa đơn")],-1)),e("div",Ue,[e("div",Ve,[t[26]||(t[26]=e("h4",{class:"font-medium text-gray-700 dark:text-gray-300 mb-2"},"Thông tin khách hàng",-1)),e("div",Ie,[e("div",null,[t[24]||(t[24]=e("p",{class:"text-sm text-gray-600 dark:text-gray-400"},"Họ tên:",-1)),e("p",Ne,c(((_=s.selectedCustomer)==null?void 0:_.full_name)||"Chưa chọn"),1)]),e("div",null,[t[25]||(t[25]=e("p",{class:"text-sm text-gray-600 dark:text-gray-400"},"Số điện thoại:",-1)),e("p",Re,c(((w=s.selectedCustomer)==null?void 0:w.phone_number)||"N/A"),1)])])]),e("div",qe,[e("table",Me,[t[27]||(t[27]=e("thead",{class:"bg-gray-50 dark:bg-dark-surface/50"},[e("tr",null,[e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"}," Sản phẩm/Dịch vụ"),e("th",{class:"px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"}," Đơn giá"),e("th",{class:"px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"}," Số lượng"),e("th",{class:"px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"}," Thành tiền")])],-1)),e("tbody",Fe,[(l(!0),u(f,null,x(s.form.order_items,o=>(l(),u("tr",{key:o.item_id},[e("td",De,[e("div",Ee,c(o.search),1),o.item_type==="service"?(l(),u("div",Le,c(o.service_type==="single"?"Đơn lẻ":o.service_type==="combo_5"?"Combo 5 lần":o.service_type==="combo_10"?"Combo 10 lần":""),1)):h("",!0)]),e("td",Ae,c(s.formatCurrency(o.price)),1),e("td",Oe,c(o.quantity),1),e("td",He,c(s.formatCurrency(o.quantity*o.price)),1)]))),128))])])]),e("div",Be,[e("div",je,[t[28]||(t[28]=e("span",{class:"text-gray-600 dark:text-gray-400"},"Tổng tiền:",-1)),e("span",Ke,c(s.formatCurrency(s.calculateTotal())),1)]),e("div",Pe,[t[29]||(t[29]=e("span",{class:"text-gray-600 dark:text-gray-400"},"Giảm giá:",-1)),e("span",ze,"-"+c(s.formatCurrency(s.calculateDiscount())),1)]),e("div",Ge,[t[30]||(t[30]=e("span",null,"Thành tiền:",-1)),e("span",Xe,c(s.formatCurrency(s.calculateFinalTotal())),1)])])])])):h("",!0)])])]}),_:1})]),_:1})}const Lt=Q(W,[["render",Je],["__scopeId","data-v-9267b8da"]]);export{Lt as default};