import{Z as v}from"./@inertiajs-D0DMCEQB.js";import{L as y}from"./LayoutAuthenticated-CBLzeGQR.js";import{_ as V}from"./SectionMain-Vot1TOkK.js";import{b as $,d as C,c as B}from"./@mdi-DWfqH7fj.js";import{_ as k}from"./BaseButton-eMTGofBM.js";import{_ as g}from"./CardBox-jfYP3QDG.js";import{aU as i,a2 as d,a3 as t,j as f,r as b,o as T,a0 as E,bH as p,aa as r,bl as u,F as L,b0 as P,bf as o}from"./@vue-BRgAy5zV.js";import{a as w}from"./axios-CCb-kr4I.js";import{u as S}from"./vue-toastification-ejyjJRED.js";import"./deepmerge-CtOfIP5S.js";import"./call-bind-aBC2DkHY.js";import"./get-intrinsic-BKEvijrG.js";import"./es-errors-DzOT6E3C.js";import"./has-symbols-eVqrYdw7.js";import"./has-proto-JnoBQRdH.js";import"./function-bind-BbkWVFrm.js";import"./hasown-DYqjtFKE.js";import"./set-function-length-B5OANX0j.js";import"./define-data-property-DO9o5wXF.js";import"./es-define-property-tzmkNPou.js";import"./gopd-CEkvUycD.js";import"./has-property-descriptors-DphFXkuo.js";import"./qs-Bnt7aRCy.js";import"./side-channel-FJR906QQ.js";import"./object-inspect-D3SFxae_.js";import"./nprogress-uqLJ5xmn.js";import"./lodash.clonedeep-Bxvn-V0B.js";import"./lodash.isequal-BYpQg7Um.js";import"./app-OyFPVnvx.js";import"./jspdf-Ciebq7We.js";import"./@babel-BsCcpEdk.js";import"./fflate-EnPhvkmS.js";import"./laravel-echo-C9ppFO4I.js";import"./pusher-js-Cl-1XqxI.js";import"./laravel-vite-plugin-DEL3ZhID.js";import"./pinia-BhnhDyRZ.js";import"./firebase-DL3QX-oU.js";import"./@firebase--zA4YiqN.js";import"./idb-Bn1DMRyg.js";import"./BaseIcon-B4kwkOCn.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";import"./colors-S7_1wET5.js";import"./OverlayLayer-BSs7OF78.js";const U={__name:"BaseToggleButton",props:{modelValue:{type:Boolean,default:!1}},emits:["update:modelValue"],setup(l,{emit:m}){const c=m;return(x,n)=>(i(),d("button",{onClick:n[0]||(n[0]=h=>c("update:modelValue",!l.modelValue)),class:"inline-flex items-center"},[t("div",{class:f(["relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 ease-in-out",l.modelValue?"bg-green-600":"bg-gray-300 dark:bg-gray-600"])},[t("span",{class:f(["inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 ease-in-out",l.modelValue?"translate-x-6":"translate-x-1"])},null,2)],2)]))}},j={class:"flex justify-between items-center"},F={key:0,class:"text-center py-4 dark:text-dark-text"},G={key:1,class:"overflow-x-auto"},M={class:"w-full text-left table-auto"},N={class:"p-4 dark:text-dark-text"},Q={class:"font-medium"},z={class:"text-sm text-gray-500 dark:text-dark-muted mt-1"},A={class:"p-4 dark:text-dark-text whitespace-nowrap"},D={class:"p-4 dark:text-dark-text font-medium"},H={class:"p-4 dark:text-dark-text"},I={class:"p-4 dark:text-dark-text"},Z={class:"p-4 dark:text-dark-text whitespace-nowrap"},q={class:"space-y-1"},J={class:"flex items-center space-x-2"},K={class:"flex items-center space-x-2"},O={class:"p-4"},R={class:"p-4"},W={class:"flex items-center gap-2"},At={__name:"Index",setup(l){const m=b([]),c=b(!0),x=S(),n=async()=>{try{const a=await w.get("/api/vouchers");m.value=a.data.data}catch(a){console.error("Error fetching vouchers:",a)}finally{c.value=!1}},h=async a=>{try{await w.patch(`/api/vouchers/${a.id}/toggle-status`),x.success("Cập nhật trạng thái voucher thành công"),await n()}catch(s){console.error("Error toggling voucher status:",s),x.error("Có lỗi xảy ra khi cập nhật trạng thái voucher")}};return T(()=>{n()}),(a,s)=>(i(),E(y,null,{default:p(()=>[r(u(v),{title:"Quản lý Voucher"}),r(V,null,{default:p(()=>[r(g,{class:"mb-6 dark:bg-dark-surface"},{default:p(()=>[t("div",j,[s[1]||(s[1]=t("h1",{class:"text-2xl font-bold dark:text-dark-text"},"Quản lý Voucher",-1)),r(k,{icon:u($),color:"info",label:"Thêm Voucher",onClick:s[0]||(s[0]=e=>a.$inertia.visit(a.route("vouchers.create")))},null,8,["icon"])])]),_:1}),r(g,{class:"dark:bg-dark-surface"},{default:p(()=>[c.value?(i(),d("div",F," Đang tải... ")):(i(),d("div",G,[t("table",M,[s[4]||(s[4]=t("thead",null,[t("tr",{class:"border-b dark:border-dark-border"},[t("th",{class:"p-4 dark:text-dark-text text-sm font-medium whitespace-nowrap"},"Mã"),t("th",{class:"p-4 dark:text-dark-text text-sm font-medium whitespace-nowrap"},"Loại giảm giá "),t("th",{class:"p-4 dark:text-dark-text text-sm font-medium whitespace-nowrap"},"Giá trị"),t("th",{class:"p-4 dark:text-dark-text text-sm font-medium whitespace-nowrap"},"Đơn tối thiểu "),t("th",{class:"p-4 dark:text-dark-text text-sm font-medium whitespace-nowrap"},"Giảm tối đa "),t("th",{class:"p-4 dark:text-dark-text text-sm font-medium whitespace-nowrap"},"Thời gian"),t("th",{class:"p-4 dark:text-dark-text text-sm font-medium whitespace-nowrap"},"Trạng thái "),t("th",{class:"p-4 dark:text-dark-text text-sm font-medium whitespace-nowrap"},"Thao tác")])],-1)),t("tbody",null,[(i(!0),d(L,null,P(m.value,e=>(i(),d("tr",{key:e.id,class:"border-b hover:bg-gray-50 dark:border-dark-border dark:hover:bg-dark-hover transition-colors duration-150 ease-in-out"},[t("td",N,[t("div",Q,o(e.code),1),t("div",z,o(e.description),1)]),t("td",A,[t("span",{class:f(["inline-flex px-2.5 py-1 rounded-full text-xs font-medium",e.discount_type==="percentage"?"bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200":"bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200"])},o(e.discount_type==="percentage"?"Phần trăm":"Số tiền cố định"),3)]),t("td",D,o(e.formatted_discount),1),t("td",H,o(e.min_order_value_formatted),1),t("td",I,o(e.max_discount_amount_formatted),1),t("td",Z,[t("div",q,[t("div",J,[s[2]||(s[2]=t("span",{class:"text-sm text-gray-500 dark:text-dark-muted"},"Từ:",-1)),t("span",null,o(e.start_date_formatted),1)]),t("div",K,[s[3]||(s[3]=t("span",{class:"text-sm text-gray-500 dark:text-dark-muted"},"Đến:",-1)),t("span",null,o(e.end_date_formatted),1)])])]),t("td",O,[r(U,{"model-value":e.status==="active","onUpdate:modelValue":_=>h(e)},null,8,["model-value","onUpdate:modelValue"])]),t("td",R,[t("div",W,[r(k,{icon:u(C),color:"info",small:"",class:"!p-2",onClick:_=>a.$inertia.visit(a.route("vouchers.edit",e.id))},null,8,["icon","onClick"]),r(k,{icon:u(B),color:"success",small:"",class:"!p-2",onClick:_=>a.$inertia.visit(a.route("vouchers.show",e.id))},null,8,["icon","onClick"])])])]))),128))])])]))]),_:1})]),_:1})]),_:1}))}};export{At as default};