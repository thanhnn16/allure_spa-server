import{Z as V,F as y}from"./@inertiajs-D0DMCEQB.js";import{L as D}from"./LayoutAuthenticated-CfR_jrFF.js";import{_ as F}from"./SectionMain-Vot1TOkK.js";import{P as U}from"./Pagination-CjFMuWLP.js";import{r as C,aL as A,b2 as m,aU as d,a0 as H,bH as I,aa as k,a3 as t,a2 as c,bf as l,a1 as T,bJ as N,bz as L,bA as M,F as B,b0 as E,j as P,a9 as j}from"./@vue-BRgAy5zV.js";import{a as R}from"./axios-CCb-kr4I.js";import{_ as z}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./deepmerge-CtOfIP5S.js";import"./call-bind-aBC2DkHY.js";import"./get-intrinsic-BKEvijrG.js";import"./es-errors-DzOT6E3C.js";import"./has-symbols-eVqrYdw7.js";import"./has-proto-JnoBQRdH.js";import"./function-bind-BbkWVFrm.js";import"./hasown-DYqjtFKE.js";import"./set-function-length-B5OANX0j.js";import"./define-data-property-DO9o5wXF.js";import"./es-define-property-tzmkNPou.js";import"./gopd-CEkvUycD.js";import"./has-property-descriptors-DphFXkuo.js";import"./qs-Bnt7aRCy.js";import"./side-channel-FJR906QQ.js";import"./object-inspect-D3SFxae_.js";import"./nprogress-uqLJ5xmn.js";import"./lodash.clonedeep-Bxvn-V0B.js";import"./lodash.isequal-BYpQg7Um.js";import"./@mdi-xfgXKVp3.js";import"./app-BzqnW5sK.js";import"./jspdf-Ciebq7We.js";import"./@babel-BsCcpEdk.js";import"./fflate-EnPhvkmS.js";import"./laravel-echo-C9ppFO4I.js";import"./pusher-js-Cl-1XqxI.js";import"./laravel-vite-plugin-DEL3ZhID.js";import"./pinia-BhnhDyRZ.js";import"./vue-toastification-ejyjJRED.js";import"./firebase-DL3QX-oU.js";import"./@firebase--zA4YiqN.js";import"./idb-Bn1DMRyg.js";import"./BaseIcon-B4kwkOCn.js";import"./colors-S7_1wET5.js";import"./OverlayLayer-BSs7OF78.js";const K={components:{Head:V,LayoutAuthenticated:D,SectionMain:F,Pagination:U},props:{invoices:{type:Object,required:!0,default:()=>({data:[],links:[],meta:{}})},error:{type:String,default:null}},setup(O){const e=C({status:"",search:""}),s=C(null);A(()=>{s.value&&clearTimeout(s.value)});const r=o=>new Intl.NumberFormat("vi-VN",{style:"currency",currency:"VND"}).format(o),b=o=>{switch(o){case"pending":return"px-3 py-1 text-sm font-medium rounded-full bg-yellow-50 text-yellow-700";case"partial":return"px-3 py-1 text-sm font-medium rounded-full bg-blue-50 text-blue-700";case"paid":return"px-3 py-1 text-sm font-medium rounded-full bg-green-50 text-green-700";case"cancelled":return"px-3 py-1 text-sm font-medium rounded-full bg-red-50 text-red-700";default:return"px-3 py-1 text-sm font-medium rounded-full bg-gray-50 text-gray-700"}},w=o=>{switch(o){case"pending":return"bg-yellow-400";case"partial":return"bg-blue-400";case"paid":return"bg-green-400";case"cancelled":return"bg-red-400";default:return"bg-gray-400"}},p=o=>{switch(o){case"pending":return"Chờ thanh toán";case"partial":return"Thanh toán một phần";case"paid":return"Đã thanh toán";case"cancelled":return"Đã hủy";default:return"Không xác định"}},x=o=>{y.visit(`/invoices/${o}`)},u=()=>{y.get("/invoices",{status:e.value.status,search:e.value.search},{preserveState:!0,preserveScroll:!0,replace:!0})};return{filters:e,formatCurrency:r,getStatusClass:b,getStatusDotClass:w,getStatusText:p,viewInvoiceDetails:x,applyFilters:u,createNewInvoice:()=>{y.visit("/invoices/create")},calculateRemainingAmount:o=>o.total_amount-o.paid_amount,testPayOS:async()=>{var o,_,S;try{const i=window.location.origin,n=await R.post("/api/payos/test",{amount:2e3,description:"Test PayOS payment",returnUrl:`${i}/success`,cancelUrl:`${i}/cancel`});if(n.data.success&&n.data.checkoutUrl)n.data.orderCode&&localStorage.setItem("payos_order_code",n.data.orderCode),window.location.href=n.data.checkoutUrl;else throw new Error("Invalid PayOS response")}catch(i){console.error("PayOS test error:",i),console.error("Error response:",(o=i.response)==null?void 0:o.data);let n="Error initiating PayOS payment";(S=(_=i.response)==null?void 0:_.data)!=null&&S.message&&(n+=`: ${i.response.data.message}`),alert(n)}},debounceSearch:()=>{s.value&&clearTimeout(s.value),s.value=setTimeout(()=>{u()},500)},truncateId:o=>o?o.length<=8?o:o.substring(0,8)+"...":""}}},Q={class:"container mx-auto px-4 py-8"},q={key:0,class:"mb-4 bg-red-100 dark:bg-red-200 border border-red-400 text-red-700 dark:text-red-800 px-4 py-3 rounded"},J={class:"flex justify-between items-center mb-6"},X={class:"space-x-4"},Z={class:"mb-6 flex flex-wrap items-center gap-4"},G={class:"flex items-center space-x-4 flex-grow"},W={key:1,class:"overflow-x-auto bg-white dark:bg-dark-surface shadow-md rounded-lg"},Y={class:"min-w-full divide-y divide-gray-200 dark:divide-dark-border"},$={class:"bg-white dark:bg-dark-surface divide-y divide-gray-200 dark:divide-dark-border"},tt={class:"px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-dark-text"},et={title:"#{{ invoice.id }}",class:"cursor-help"},rt={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-dark-text-muted"},at={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-dark-text-muted"},ot={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-dark-text-muted"},st={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-dark-text-muted"},nt={class:"px-6 py-4 whitespace-nowrap"},it={class:"px-6 py-4 whitespace-nowrap text-sm"},dt=["onClick"],lt={key:2,class:"bg-white dark:bg-dark-surface shadow-md rounded-lg p-8 text-center"},ct={key:3,class:"mt-6"};function ut(O,e,s,r,b,w){const p=m("Head"),x=m("Pagination"),u=m("SectionMain"),v=m("LayoutAuthenticated");return d(),H(v,null,{default:I(()=>[k(p,{title:"Quản lý hóa đơn"}),k(u,null,{default:I(()=>{var g,h,f;return[t("div",Q,[s.error?(d(),c("div",q,l(s.error),1)):T("",!0),t("div",J,[e[7]||(e[7]=t("h1",{class:"text-2xl font-semibold text-gray-900 dark:text-dark-text"},"Quản lý hóa đơn",-1)),t("div",X,[t("button",{onClick:e[0]||(e[0]=(...a)=>r.testPayOS&&r.testPayOS(...a)),class:"bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded"}," Test PayOS (2,000 VNĐ) "),t("button",{onClick:e[1]||(e[1]=(...a)=>r.createNewInvoice&&r.createNewInvoice(...a)),class:"bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded"}," Tạo hóa đơn mới ")])]),t("div",Z,[t("div",G,[N(t("select",{"onUpdate:modelValue":e[2]||(e[2]=a=>r.filters.status=a),onChange:e[3]||(e[3]=(...a)=>r.applyFilters&&r.applyFilters(...a)),class:"form-select rounded-md shadow-sm w-48 bg-white dark:bg-dark-surface text-gray-900 dark:text-dark-text"},e[8]||(e[8]=[t("option",{value:""},"Tất cả trạng thái",-1),t("option",{value:"pending"},"Chờ thanh toán",-1),t("option",{value:"partial"},"Thanh toán một phần",-1),t("option",{value:"paid"},"Đã thanh toán",-1),t("option",{value:"cancelled"},"Đã hủy",-1)]),544),[[L,r.filters.status]]),N(t("input",{"onUpdate:modelValue":e[4]||(e[4]=a=>r.filters.search=a),onInput:e[5]||(e[5]=(...a)=>r.debounceSearch&&r.debounceSearch(...a)),type:"text",placeholder:"Tìm kiếm theo ID hoặc tên khách hàng",class:"form-input rounded-md shadow-sm flex-grow bg-white dark:bg-dark-surface text-gray-900 dark:text-dark-text"},null,544),[[M,r.filters.search]])])]),((h=(g=s.invoices)==null?void 0:g.data)==null?void 0:h.length)>0?(d(),c("div",W,[t("table",Y,[e[9]||(e[9]=t("thead",null,[t("tr",null,[t("th",{class:"px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-dark-text-muted uppercase tracking-wider"}," ID Hóa đơn "),t("th",{class:"px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-dark-text-muted uppercase tracking-wider"}," Khách hàng "),t("th",{class:"px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-dark-text-muted uppercase tracking-wider"}," Tổng tiền "),t("th",{class:"px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-dark-text-muted uppercase tracking-wider"}," Đã thanh toán "),t("th",{class:"px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-dark-text-muted uppercase tracking-wider"}," Còn lại "),t("th",{class:"px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-dark-text-muted uppercase tracking-wider"}," Trạng thái "),t("th",{class:"px-6 py-4 bg-gray-50 dark:bg-dark-surface text-left text-xs font-medium text-gray-500 dark:text-dark-text-muted uppercase tracking-wider"}," Hành động ")])],-1)),t("tbody",$,[(d(!0),c(B,null,E(s.invoices.data,a=>(d(),c("tr",{key:a.id,class:"hover:bg-gray-50 dark:hover:bg-dark-surface-hover"},[t("td",tt,[t("span",et," #"+l(r.truncateId(a.id)),1)]),t("td",rt,l(a.user.full_name),1),t("td",at,l(r.formatCurrency(a.total_amount)),1),t("td",ot,l(r.formatCurrency(a.paid_amount)),1),t("td",st,l(r.formatCurrency(r.calculateRemainingAmount(a))),1),t("td",nt,[t("span",{class:P([r.getStatusClass(a.status),"flex items-center w-fit"])},[t("span",{class:P(["h-2 w-2 rounded-full mr-2",r.getStatusDotClass(a.status)])},null,2),j(" "+l(r.getStatusText(a.status)),1)],2)]),t("td",it,[t("button",{onClick:o=>r.viewInvoiceDetails(a.id),class:"text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-500 font-medium hover:underline"}," Xem chi tiết ",8,dt)])]))),128))])])])):(d(),c("div",lt,[e[10]||(e[10]=t("p",{class:"text-gray-600 dark:text-dark-text-muted text-lg"},"Chưa có dữ liệu hóa đơn.",-1)),t("button",{onClick:e[6]||(e[6]=(...a)=>r.createNewInvoice&&r.createNewInvoice(...a)),class:"mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded"}," Tạo hóa đơn đầu tiên ")])),(f=s.invoices)!=null&&f.links?(d(),c("div",ct,[k(x,{links:s.invoices.links,class:"mt-6"},null,8,["links"])])):T("",!0)])]}),_:1})]),_:1})}const te=z(K,[["render",ut]]);export{te as default};