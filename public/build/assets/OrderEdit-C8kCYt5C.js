import{Z as v,i as _}from"./@inertiajs-D0DMCEQB.js";import{L as w}from"./LayoutAuthenticated-CBLzeGQR.js";import{_ as k}from"./SectionMain-Vot1TOkK.js";import{r as V,b2 as l,aU as u,a0 as S,bH as c,aa as p,a3 as o,bf as L,a9 as U,bM as C,bJ as d,bz as F,a2 as g,b0 as M,bA as m,F as N}from"./@vue-BRgAy5zV.js";import{a as D}from"./axios-CCb-kr4I.js";import{_ as H}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./deepmerge-CtOfIP5S.js";import"./call-bind-aBC2DkHY.js";import"./get-intrinsic-BKEvijrG.js";import"./es-errors-DzOT6E3C.js";import"./has-symbols-eVqrYdw7.js";import"./has-proto-JnoBQRdH.js";import"./function-bind-BbkWVFrm.js";import"./hasown-DYqjtFKE.js";import"./set-function-length-B5OANX0j.js";import"./define-data-property-DO9o5wXF.js";import"./es-define-property-tzmkNPou.js";import"./gopd-CEkvUycD.js";import"./has-property-descriptors-DphFXkuo.js";import"./qs-Bnt7aRCy.js";import"./side-channel-FJR906QQ.js";import"./object-inspect-D3SFxae_.js";import"./nprogress-uqLJ5xmn.js";import"./lodash.clonedeep-Bxvn-V0B.js";import"./lodash.isequal-BYpQg7Um.js";import"./@mdi-DWfqH7fj.js";import"./app-OyFPVnvx.js";import"./jspdf-Ciebq7We.js";import"./@babel-BsCcpEdk.js";import"./fflate-EnPhvkmS.js";import"./laravel-echo-C9ppFO4I.js";import"./pusher-js-Cl-1XqxI.js";import"./laravel-vite-plugin-DEL3ZhID.js";import"./pinia-BhnhDyRZ.js";import"./vue-toastification-ejyjJRED.js";import"./firebase-DL3QX-oU.js";import"./@firebase--zA4YiqN.js";import"./idb-Bn1DMRyg.js";import"./BaseIcon-B4kwkOCn.js";import"./colors-S7_1wET5.js";import"./OverlayLayer-BSs7OF78.js";const A={components:{Head:v,Link:_,LayoutAuthenticated:w,SectionMain:k},props:{order:Object},setup(r){const t=V({status:r.order.status,order_items:r.order.order_items,note:r.order.note});return{form:t,formatCurrency:n=>new Intl.NumberFormat("vi-VN",{style:"currency",currency:"VND"}).format(n),submitForm:async()=>{try{const n=await D.put(`/api/orders/${r.order.id}`,t.value);console.log("Order updated:",n.data)}catch(n){console.error("Error updating order:",n)}}}}},B={class:"container mx-auto px-4 py-8"},T={class:"flex justify-between items-center mb-6"},j={class:"text-2xl font-semibold"},q={class:"bg-white shadow overflow-hidden sm:rounded-lg"},E={class:"px-4 py-5 sm:p-6"},O={class:"grid grid-cols-6 gap-6"},z={class:"col-span-6 sm:col-span-3"},G={class:"col-span-6"},I={class:"grid grid-cols-6 gap-6"},J={class:"col-span-6 sm:col-span-3"},Q=["onUpdate:modelValue"],Z={class:"col-span-6 sm:col-span-3"},K=["value"],P={class:"col-span-6 sm:col-span-2"},R=["onUpdate:modelValue"],W={class:"col-span-6 sm:col-span-2"},X=["onUpdate:modelValue"],Y={class:"col-span-6 sm:col-span-2"},$=["value"],oo={class:"col-span-6"};function to(r,t,a,s,n,eo){const f=l("Head"),b=l("Link"),y=l("SectionMain"),x=l("LayoutAuthenticated");return u(),S(x,null,{default:c(()=>[p(f,{title:`Sửa đơn hàng #${a.order.id}`},null,8,["title"]),p(y,null,{default:c(()=>[o("div",B,[o("div",T,[o("h1",j,"Sửa đơn hàng #"+L(a.order.id),1),p(b,{href:r.route("orders.show",a.order.id),class:"bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded"},{default:c(()=>t[3]||(t[3]=[U(" Quay lại ")])),_:1},8,["href"])]),o("form",{onSubmit:t[2]||(t[2]=C((...e)=>s.submitForm&&s.submitForm(...e),["prevent"]))},[o("div",q,[o("div",E,[o("div",O,[o("div",z,[t[5]||(t[5]=o("label",{for:"status",class:"block text-sm font-medium text-gray-700"},"Trạng thái",-1)),d(o("select",{id:"status","onUpdate:modelValue":t[0]||(t[0]=e=>s.form.status=e),class:"mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"},t[4]||(t[4]=[o("option",{value:"pending"},"Chờ xác nhận",-1),o("option",{value:"confirmed"},"Đã xác nhận",-1),o("option",{value:"shipping"},"Đang giao hàng",-1),o("option",{value:"delivered"},"Đã giao hàng",-1),o("option",{value:"completed"},"Hoàn thành",-1),o("option",{value:"cancelled"},"Đã hủy",-1)]),512),[[F,s.form.status]])]),o("div",G,[t[11]||(t[11]=o("h3",{class:"text-lg font-medium leading-6 text-gray-900 mb-4"},"Các mục trong đơn hàng",-1)),(u(!0),g(N,null,M(s.form.order_items,(e,h)=>(u(),g("div",{key:h,class:"mb-4 p-4 border rounded-md"},[o("div",I,[o("div",J,[t[6]||(t[6]=o("label",{class:"block text-sm font-medium text-gray-700"},"Sản phẩm/Dịch vụ",-1)),d(o("input",{type:"text","onUpdate:modelValue":i=>e.item_name=i,class:"mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md",readonly:""},null,8,Q),[[m,e.item_name]])]),o("div",Z,[t[7]||(t[7]=o("label",{class:"block text-sm font-medium text-gray-700"},"Loại",-1)),o("input",{type:"text",value:e.item_type==="product"?"Sản phẩm":"Dịch vụ",class:"mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md",readonly:""},null,8,K)]),o("div",P,[t[8]||(t[8]=o("label",{class:"block text-sm font-medium text-gray-700"},"Số lượng",-1)),d(o("input",{type:"number","onUpdate:modelValue":i=>e.quantity=i,min:"1",class:"mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"},null,8,R),[[m,e.quantity,void 0,{number:!0}]])]),o("div",W,[t[9]||(t[9]=o("label",{class:"block text-sm font-medium text-gray-700"},"Đơn giá",-1)),d(o("input",{type:"number","onUpdate:modelValue":i=>e.price=i,min:"0",class:"mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"},null,8,X),[[m,e.price,void 0,{number:!0}]])]),o("div",Y,[t[10]||(t[10]=o("label",{class:"block text-sm font-medium text-gray-700"},"Thành tiền",-1)),o("input",{type:"text",value:s.formatCurrency(e.quantity*e.price),class:"mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md",readonly:""},null,8,$)])])]))),128))]),o("div",oo,[t[12]||(t[12]=o("label",{for:"note",class:"block text-sm font-medium text-gray-700"},"Ghi chú",-1)),d(o("textarea",{id:"note","onUpdate:modelValue":t[1]||(t[1]=e=>s.form.note=e),rows:"3",class:"mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"},null,512),[[m,s.form.note]])])])]),t[13]||(t[13]=o("div",{class:"px-4 py-3 bg-gray-50 text-right sm:px-6"},[o("button",{type:"submit",class:"inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"}," Lưu thay đổi ")],-1))])],32)])]),_:1})]),_:1})}const Qo=H(A,[["render",to]]);export{Qo as default};