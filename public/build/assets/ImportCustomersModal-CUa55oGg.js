import{r as d,aU as o,a0 as L,bH as M,a3 as a,j as g,bl as l,a2 as i,aa as c,a9 as T,bf as p,bM as X,a1 as h,F as E,b0 as H}from"./@vue-BRgAy5zV.js";import{T as U}from"./@inertiajs-D0DMCEQB.js";import{C as O}from"./CardBoxModal-Lj_lwZN_.js";import{_}from"./BaseButton-eMTGofBM.js";import{J as y,j as $,K as z,L as J}from"./@mdi-DWfqH7fj.js";import{_ as b}from"./BaseIcon-B4kwkOCn.js";import"./axios-CCb-kr4I.js";import"./deepmerge-CtOfIP5S.js";import"./call-bind-aBC2DkHY.js";import"./get-intrinsic-BKEvijrG.js";import"./es-errors-DzOT6E3C.js";import"./has-symbols-eVqrYdw7.js";import"./has-proto-JnoBQRdH.js";import"./function-bind-BbkWVFrm.js";import"./hasown-DYqjtFKE.js";import"./set-function-length-B5OANX0j.js";import"./define-data-property-DO9o5wXF.js";import"./es-define-property-tzmkNPou.js";import"./gopd-CEkvUycD.js";import"./has-property-descriptors-DphFXkuo.js";import"./qs-Bnt7aRCy.js";import"./side-channel-FJR906QQ.js";import"./object-inspect-D3SFxae_.js";import"./nprogress-uqLJ5xmn.js";import"./lodash.clonedeep-Bxvn-V0B.js";import"./lodash.isequal-BYpQg7Um.js";import"./BaseButtons-B17KSMwc.js";import"./CardBox-jfYP3QDG.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";import"./OverlayLayer-BSs7OF78.js";import"./colors-S7_1wET5.js";const K={class:"p-6"},R={class:"mb-6"},q={class:"flex items-center justify-center w-full"},A={key:0,class:"flex flex-col items-center justify-center pt-5 pb-6"},G={key:1,class:"flex items-center p-2 rounded dark:bg-slate-600"},P={class:"text-sm font-medium text-gray-700 dark:text-slate-200"},Q={key:0,class:"mt-2 text-sm text-red-600 dark:text-red-400"},W={key:0,class:"mb-4"},Y={key:1,class:"mb-4"},Z={class:"flex justify-end space-x-2"},Fe={__name:"ImportCustomersModal",props:{modelValue:Boolean},emits:["update:modelValue","imported","close"],setup(C,{emit:V}){const m=V,r=U({file:null}),B=d(null),f=d(""),u=d(!1),s=d(""),n=d(null),D=e=>{const t=e.target.files[0];t&&v(t)},v=e=>{r.file=e,f.value=e.name,s.value="",n.value=null},S=e=>{e.preventDefault(),u.value=!0},w=()=>{u.value=!1},N=e=>{e.preventDefault(),u.value=!1;const t=e.dataTransfer.files[0];t&&v(t)},k=()=>{r.file=null,f.value="",s.value="",n.value=null},j=()=>{if(!r.file){alert("Vui lòng chọn file trước khi xác nhận.");return}s.value="Đang xử lý...",r.post(route("users.import"),{preserveState:!0,preserveScroll:!0,onSuccess:e=>{m("update:modelValue",!1),m("imported",e)},onError:e=>{console.error("Import failed",e),s.value="Có lỗi xảy ra. Vui lòng kiểm tra thông báo lỗi.",n.value=e}})},F=()=>{k(),m("update:modelValue",!1)};return(e,t)=>(o(),L(O,{modelValue:C.modelValue,"onUpdate:modelValue":t[0]||(t[0]=x=>m("update:modelValue",x)),title:"Nhập khách hàng từ file Excel",hasCancel:!1,hasButton:!1,class:"dark:bg-slate-800"},{default:M(()=>[a("div",K,[a("div",R,[a("div",q,[a("label",{for:"file-upload",class:g(["flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 dark:border-slate-600 dark:bg-slate-700 dark:hover:bg-slate-600 dark:hover:border-slate-500",{"border-blue-500 bg-blue-50 dark:border-blue-500 dark:bg-slate-600":u.value}]),onDragover:S,onDragleave:w,onDrop:N},[l(r).file?(o(),i("div",G,[c(b,{path:l(y),class:"w-6 h-6 mr-2 text-gray-600 dark:text-slate-300"},null,8,["path"]),a("span",P,p(f.value),1),a("button",{type:"button",onClick:X(k,["prevent"]),class:"ml-2 text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"},[c(b,{path:l($),class:"w-5 h-5"},null,8,["path"])])])):(o(),i("div",A,[c(b,{path:l(y),class:"w-10 h-10 mb-3 text-gray-400 dark:text-slate-300"},null,8,["path"]),t[1]||(t[1]=a("p",{class:"mb-2 text-sm text-gray-500 dark:text-slate-300"},[a("span",{class:"font-semibold"},"Nhấp để tải lên"),T(" hoặc kéo và thả ")],-1)),t[2]||(t[2]=a("p",{class:"text-xs text-gray-500 dark:text-slate-400"}," XLSX, XLS, hoặc CSV (Tối đa 10MB) ",-1))])),a("input",{id:"file-upload",ref_key:"fileInput",ref:B,type:"file",class:"hidden",onChange:D,accept:".xlsx,.xls,.csv"},null,544)],34)]),l(r).errors.file?(o(),i("p",Q,p(l(r).errors.file),1)):h("",!0)]),s.value?(o(),i("div",W,[a("p",{class:g({"text-blue-600 dark:text-blue-400":s.value==="Đang xử lý...","text-green-600 dark:text-green-400":s.value==="Hoàn thành","text-red-600 dark:text-red-400":s.value==="Có lỗi xảy ra. Vui lòng kiểm tra thông báo lỗi."})},p(s.value),3)])):h("",!0),n.value?(o(),i("div",Y,[(o(!0),i(E,null,H(n.value,(x,I)=>(o(),i("p",{key:I,class:"text-red-600 dark:text-red-400"},p(x),1))),128))])):h("",!0),a("div",Z,[c(_,{type:"button",color:"info",icon:l(z),label:"Xác nhận",onClick:j,disabled:l(r).processing||!l(r).file,class:"dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200"},null,8,["icon","disabled"]),c(_,{type:"button",color:"danger",icon:l(J),label:"Hủy",onClick:F,disabled:l(r).processing,class:"dark:bg-red-700 dark:hover:bg-red-600 dark:text-slate-200"},null,8,["icon","disabled"])])])]),_:1},8,["modelValue"]))}};export{Fe as default};