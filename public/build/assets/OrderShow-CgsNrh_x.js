import{Z as _e,i as Ce,F as I}from"./@inertiajs-D0DMCEQB.js";import{L as we}from"./LayoutAuthenticated-XZirOlYW.js";import{_ as Se}from"./SectionMain-BbIPmLtw.js";import{_ as Te}from"./CardBox-jfYP3QDG.js";import{c as V,aU as g,a2 as v,a3 as e,j as B,a9 as _,bf as u,r as x,e as H,a0 as N,bH as C,bJ as O,F as G,b0 as K,bz as Ve,bA as D,a1 as w,b2 as M,aa as T}from"./@vue-BRgAy5zV.js";import{C as E}from"./CardBoxModal-COZ15ICG.js";import{a as j}from"./axios-CCb-kr4I.js";import{u as Me}from"./vue-toastification-ejyjJRED.js";import{_ as Ie}from"./BaseIcon-B4kwkOCn.js";import{_ as Be}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./deepmerge-CtOfIP5S.js";import"./call-bind-aBC2DkHY.js";import"./get-intrinsic-BKEvijrG.js";import"./es-errors-DzOT6E3C.js";import"./has-symbols-eVqrYdw7.js";import"./has-proto-JnoBQRdH.js";import"./function-bind-BbkWVFrm.js";import"./hasown-DYqjtFKE.js";import"./set-function-length-B5OANX0j.js";import"./define-data-property-DO9o5wXF.js";import"./es-define-property-tzmkNPou.js";import"./gopd-CEkvUycD.js";import"./has-property-descriptors-DphFXkuo.js";import"./qs-Bnt7aRCy.js";import"./side-channel-FJR906QQ.js";import"./object-inspect-D3SFxae_.js";import"./nprogress-uqLJ5xmn.js";import"./lodash.clonedeep-Bxvn-V0B.js";import"./lodash.isequal-BYpQg7Um.js";import"./@mdi-CTfBXXzV.js";import"./app-sTdmxqRJ.js";import"./jspdf-Ciebq7We.js";import"./@babel-BsCcpEdk.js";import"./fflate-EnPhvkmS.js";import"./laravel-echo-C9ppFO4I.js";import"./pusher-js-Cl-1XqxI.js";import"./laravel-vite-plugin-DEL3ZhID.js";import"./pinia-BhnhDyRZ.js";import"./firebase-DL3QX-oU.js";import"./@firebase--zA4YiqN.js";import"./idb-Bn1DMRyg.js";import"./colors-S7_1wET5.js";import"./OverlayLayer-BSs7OF78.js";import"./BaseButton-eMTGofBM.js";import"./BaseButtons-B17KSMwc.js";const je={__name:"StatusBadge",props:{status:{type:String,required:!0,validator:t=>["pending","confirmed","shipping","completed","cancelled"].includes(t)},size:{type:String,default:"md",validator:t=>["sm","md","lg"].includes(t)}},setup(t){const n=t,a=V(()=>`inline-flex items-center font-medium rounded-full ${{sm:"px-2 py-1 text-xs",md:"px-3 py-1.5 text-sm",lg:"px-4 py-2 text-base"}[n.size]}`),o={pending:"bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300",confirmed:"bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300",shipping:"bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300",completed:"bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300",cancelled:"bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300"},p={pending:"bg-yellow-400",confirmed:"bg-blue-400",shipping:"bg-purple-400",completed:"bg-green-400",cancelled:"bg-red-400"},f={pending:"Chờ xác nhận",confirmed:"Đã xác nhận",shipping:"Đang giao hàng",completed:"Hoàn thành",cancelled:"Đã hủy"};return(y,b)=>(g(),v("span",{class:B([a.value,o[t.status]])},[e("span",{class:B(["w-2 h-2 rounded-full mr-2",p[t.status]])},null,2),_(" "+u(f[t.status]),1)],2))}},Ue={__name:"PaymentStatusBadge",props:{status:{type:String,required:!0,validator:t=>["pending","partial","paid","cancelled"].includes(t)},size:{type:String,default:"md",validator:t=>["sm","md","lg"].includes(t)}},setup(t){const n=t,a=V(()=>`inline-flex items-center font-medium rounded-full ${{sm:"px-2 py-1 text-xs",md:"px-3 py-1.5 text-sm",lg:"px-4 py-2 text-base"}[n.size]}`),o={pending:"bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300",partial:"bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300",paid:"bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300",cancelled:"bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300"},p={pending:"bg-yellow-400",partial:"bg-blue-400",paid:"bg-green-400",cancelled:"bg-red-400"},f={pending:"Chờ thanh toán",partial:"Thanh toán một phần",paid:"Đã thanh toán",cancelled:"Đã hủy"};return(y,b)=>(g(),v("span",{class:B([a.value,o[t.status]])},[e("span",{class:B(["w-2 h-2 rounded-full mr-2",p[t.status]])},null,2),_(" "+u(f[t.status]),1)],2))}},Ne="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full",Oe={__name:"ItemTypeBadge",props:{type:{type:String,required:!0,validator:t=>["product","service"].includes(t)}},setup(t){const n={product:"bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300",service:"bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300"},a={product:"mdi-package-variant",service:"mdi-spa"},o={product:"Sản phẩm",service:"Dịch vụ"};return(p,f)=>(g(),v("span",{class:B([Ne,n[t.type]])},[e("i",{class:B(["mdi mr-1",a[t.type]])},null,2),_(" "+u(o[t.type]),1)],2))}},He={class:"space-y-4"},De=["value"],Ee={class:"flex justify-end space-x-3 pt-4"},Pe=["disabled"],Le={key:0,class:"mdi mdi-loading mdi-spin mr-2"},$e={__name:"UpdateStatusModal",props:{modelValue:Boolean,order:Object,availableStatuses:Array},emits:["update:modelValue","updated"],setup(t,{emit:n}){const a=t,o=n,p=x(a.order.status),f=x(""),y=x(!1);H(()=>a.modelValue,m=>{m||(p.value=a.order.status,f.value="",y.value=!1)});const b=m=>({pending:"Chờ xác nhận",confirmed:"Đã xác nhận",shipping:"Đang giao hàng",completed:"Hoàn thành",cancelled:"Đã hủy"})[m],i=async()=>{if(!y.value){y.value=!0;try{o("updated",{status:p.value,note:f.value})}catch(m){console.error("Error in handleSubmit:",m)}}};return(m,h)=>(g(),N(E,{"model-value":t.modelValue,"onUpdate:modelValue":h[3]||(h[3]=l=>m.$emit("update:modelValue",l)),title:"Cập nhật trạng thái đơn hàng","has-button":!1},{default:C(()=>[e("div",He,[e("div",null,[h[4]||(h[4]=e("label",{class:"block text-sm font-medium mb-2"},"Trạng thái mới",-1)),O(e("select",{"onUpdate:modelValue":h[0]||(h[0]=l=>p.value=l),class:"form-select w-full rounded-lg border-gray-300 dark:border-dark-border dark:bg-dark-surface"},[(g(!0),v(G,null,K(t.availableStatuses,l=>(g(),v("option",{key:l,value:l},u(b(l)),9,De))),128))],512),[[Ve,p.value]])]),e("div",null,[h[5]||(h[5]=e("label",{class:"block text-sm font-medium mb-2"},"Ghi chú (tùy chọn)",-1)),O(e("textarea",{"onUpdate:modelValue":h[1]||(h[1]=l=>f.value=l),rows:"3",class:"form-textarea w-full rounded-lg border-gray-300 dark:border-dark-border dark:bg-dark-surface",placeholder:"Nhập ghi chú cho việc cập nhật trạng thái"},null,512),[[D,f.value]])]),e("div",Ee,[e("button",{onClick:h[2]||(h[2]=l=>m.$emit("update:modelValue",!1)),class:"px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-border hover:bg-gray-50 dark:hover:bg-dark-bg/50 transition-colors duration-200"}," Hủy "),e("button",{onClick:i,disabled:y.value,class:"px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"},[y.value?(g(),v("i",Le)):w("",!0),h[6]||(h[6]=_(" Cập nhật "))],8,Pe)])])]),_:1},8,["model-value"]))}},ze={class:"p-6"},Ae={class:"text-lg font-medium mb-4"},Fe={class:"space-y-4"},qe={class:"bg-gray-50 dark:bg-dark-bg/50 p-4 rounded-lg"},Ge={class:"flex justify-between mb-2"},Ke={class:"font-medium"},Xe={class:"flex justify-between"},Je={class:"text-green-600"},Qe={class:"flex justify-between pt-2 border-t mt-2"},Ze={class:"font-medium"},Re={class:"mt-6 flex justify-end space-x-3"},We=["disabled"],Ye={key:0,class:"mdi mdi-loading mdi-spin mr-2"},et={__name:"CreateInvoiceModal",props:{modelValue:Boolean,order:Object},emits:["update:modelValue","created"],setup(t,{emit:n}){const a=n,o=x(""),p=x(!1),f=b=>new Intl.NumberFormat("vi-VN",{style:"currency",currency:"VND"}).format(b),y=async()=>{p.value=!0;try{await a("created",{note:o.value}),o.value=""}catch(b){console.error("Error creating invoice:",b)}finally{p.value=!1}};return(b,i)=>(g(),N(E,{"model-value":t.modelValue,"onUpdate:modelValue":i[2]||(i[2]=m=>b.$emit("update:modelValue",m)),title:"Tạo hóa đơn cho đơn hàng #{{ order.id }}","has-button":!1},{default:C(()=>[e("div",ze,[e("h3",Ae," Tạo hóa đơn cho đơn hàng #"+u(t.order.id),1),e("div",Fe,[e("div",qe,[e("div",Ge,[i[3]||(i[3]=e("span",{class:"text-gray-500"},"Tổng tiền:",-1)),e("span",Ke,u(f(t.order.total_amount)),1)]),e("div",Xe,[i[4]||(i[4]=e("span",{class:"text-gray-500"},"Giảm giá:",-1)),e("span",Je,"-"+u(f(t.order.discount_amount)),1)]),e("div",Qe,[i[5]||(i[5]=e("span",{class:"font-medium"},"Thành tiền:",-1)),e("span",Ze,u(f(t.order.total_amount-t.order.discount_amount)),1)])]),e("div",null,[i[6]||(i[6]=e("label",{class:"block text-sm font-medium mb-2"},"Ghi chú (tùy chọn)",-1)),O(e("textarea",{"onUpdate:modelValue":i[0]||(i[0]=m=>o.value=m),rows:"3",class:"form-textarea w-full",placeholder:"Nhập ghi chú cho hóa đơn"},null,512),[[D,o.value]])])]),e("div",Re,[e("button",{onClick:i[1]||(i[1]=m=>b.$emit("update:modelValue",!1)),class:"btn-secondary"}," Hủy "),e("button",{onClick:y,disabled:p.value,class:"btn-primary"},[p.value?(g(),v("i",Ye)):w("",!0),i[7]||(i[7]=_(" Tạo hóa đơn "))],8,We)])])]),_:1},8,["model-value"]))}},tt={class:"p-6"},nt={class:"text-lg font-medium mb-4"},st={class:"space-y-4"},ot={class:"mt-6 flex justify-end space-x-3"},at=["disabled"],rt={key:0,class:"mdi mdi-loading mdi-spin mr-2"},lt={__name:"CompleteOrderModal",props:{modelValue:Boolean,order:Object,hasServiceCombo:Boolean},emits:["update:modelValue","completed"],setup(t,{emit:n}){const a=t,o=n,p=x(""),f=x(!1),y=V(()=>{var i;return((i=a.order.invoice)==null?void 0:i.status)==="paid"}),b=async()=>{if(y.value){f.value=!0;try{(await j.post(`/api/orders/${a.order.id}/complete`,{note:p.value})).data.success&&(o("completed"),o("update:modelValue",!1),p.value="")}catch(i){console.error("Error completing order:",i)}finally{f.value=!1}}};return(i,m)=>(g(),N(E,{"model-value":t.modelValue,"onUpdate:modelValue":m[2]||(m[2]=h=>i.$emit("update:modelValue",h)),title:"Hoàn thành đơn hàng #{{ order.id }}","has-button":!1},{default:C(()=>[e("div",tt,[e("h3",nt," Tạo gói dịch vụ cho đơn hàng #"+u(t.order.id),1),e("div",st,[m[4]||(m[4]=e("div",{class:"bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg"},[e("div",{class:"flex"},[e("i",{class:"mdi mdi-information text-blue-400 text-xl mr-3"}),e("div",null,[e("h4",{class:"font-medium text-blue-800 dark:text-blue-300"}," Thông tin quan trọng "),e("p",{class:"text-sm text-blue-700 dark:text-blue-400"}," Hệ thống sẽ tạo gói liệu trình cho các dịch vụ combo trong đơn hàng này. ")])])],-1)),e("div",null,[m[3]||(m[3]=e("label",{class:"block text-sm font-medium mb-2"},"Ghi chú (tùy chọn)",-1)),O(e("textarea",{"onUpdate:modelValue":m[0]||(m[0]=h=>p.value=h),rows:"3",class:"form-textarea w-full",placeholder:"Nhập ghi chú khi tạo gói dịch vụ"},null,512),[[D,p.value]])])]),e("div",ot,[e("button",{onClick:m[1]||(m[1]=h=>i.$emit("update:modelValue",!1)),class:"btn-secondary"}," Hủy "),e("button",{onClick:b,disabled:f.value,class:"btn-success"},[f.value?(g(),v("i",rt)):w("",!0),m[5]||(m[5]=_(" Tạo gói dịch vụ "))],8,at)])])]),_:1},8,["model-value"]))}},dt={class:"p-6"},it={class:"text-lg font-medium mb-4"},ct={class:"space-y-4"},ut={class:"bg-gray-50 dark:bg-dark-bg/50 p-4 rounded-lg"},mt={class:"flex justify-between mb-2"},gt={class:"font-medium"},ht={key:0,class:"flex justify-between text-red-600"},pt={key:0,class:"mt-1 text-sm text-red-500"},ft={class:"mt-6 flex justify-end space-x-3"},vt=["disabled"],yt={key:0,class:"mdi mdi-loading mdi-spin mr-2"},bt={__name:"CancelOrderModal",props:{modelValue:Boolean,order:Object},emits:["update:modelValue","cancelled"],setup(t,{emit:n}){const a=t,o=n,p=x(""),f=x(!1),y=x(!1),b=V(()=>["pending","confirmed"].includes(a.order.status)&&(!a.order.invoice||a.order.invoice.status!=="paid")),i=h=>new Intl.NumberFormat("vi-VN",{style:"currency",currency:"VND"}).format(h),m=async()=>{if(b.value){if(!p.value.trim()){y.value=!0;return}f.value=!0;try{o("cancelled",{reason:p.value})}finally{f.value=!1}}};return H(p,()=>{p.value.trim()&&(y.value=!1)}),(h,l)=>(g(),N(E,{"model-value":t.modelValue,"onUpdate:modelValue":l[2]||(l[2]=k=>h.$emit("update:modelValue",k)),title:"Hủy đơn hàng #{{ order.id }}","has-button":!1},{default:C(()=>[e("div",dt,[e("h3",it," Hủy đơn hàng #"+u(t.order.id),1),e("div",ct,[l[6]||(l[6]=e("div",{class:"bg-red-50 dark:bg-red-900/30 p-4 rounded-lg"},[e("div",{class:"flex"},[e("i",{class:"mdi mdi-alert text-red-400 text-xl mr-3"}),e("div",null,[e("h4",{class:"font-medium text-red-800 dark:text-red-300"}," Cảnh báo "),e("p",{class:"text-sm text-red-700 dark:text-red-400"}," Bạn chắc chắn muốn hủy đơn hàng này? Hành động này không thể hoàn tác. ")])])],-1)),e("div",ut,[e("div",mt,[l[3]||(l[3]=e("span",{class:"text-gray-500"},"Tổng tiền:",-1)),e("span",gt,u(i(t.order.total_amount)),1)]),t.order.invoice?(g(),v("div",ht,[l[4]||(l[4]=e("span",null,"Đã thanh toán:",-1)),e("span",null,u(i(t.order.invoice.paid_amount)),1)])):w("",!0)]),e("div",null,[l[5]||(l[5]=e("label",{class:"block text-sm font-medium mb-2"},[_(" Lý do hủy đơn "),e("span",{class:"text-red-500"},"*")],-1)),O(e("textarea",{"onUpdate:modelValue":l[0]||(l[0]=k=>p.value=k),rows:"3",class:B(["form-textarea w-full",{"border-red-500":y.value}]),placeholder:"Vui lòng nhập lý do hủy đơn hàng"},null,2),[[D,p.value]]),y.value?(g(),v("p",pt," Vui lòng nhập lý do hủy đơn hàng ")):w("",!0)])]),e("div",ft,[e("button",{onClick:l[1]||(l[1]=k=>h.$emit("update:modelValue",!1)),class:"btn-secondary"}," Đóng "),e("button",{onClick:m,disabled:f.value||!b.value,class:"btn-danger"},[f.value?(g(),v("i",yt)):w("",!0),l[7]||(l[7]=_(" Xác nhận hủy "))],8,vt)])])]),_:1},8,["model-value"]))}},xt={name:"OrderShow",components:{Head:_e,Link:Ce,LayoutAuthenticated:we,SectionMain:Se,CardBox:Te,StatusBadge:je,PaymentStatusBadge:Ue,ItemTypeBadge:Oe,UpdateStatusModal:$e,CreateInvoiceModal:et,CompleteOrderModal:lt,CancelOrderModal:bt,BaseIcon:Ie},props:{order:Object},setup(t){console.log("Order data:",t.order),console.log("Invoice data:",t.order.invoice);const n=x(!1),a=x(!1),o=x(!1),p=x(!1),f=x(!1),y=x(t.order.status),b=x(""),i=x(""),m=x(""),h=x(""),l=Me(),k=x(!1),P=s=>new Intl.NumberFormat("vi-VN",{style:"currency",currency:"VND"}).format(s),L=s=>new Date(s).toLocaleDateString("vi-VN",{year:"numeric",month:"long",day:"numeric",hour:"2-digit",minute:"2-digit"}),$=s=>{const r="px-2 inline-flex text-xs leading-5 font-semibold rounded-full items-center transition-colors duration-150";return{pending:`${r} bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300`,confirmed:`${r} bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300`,shipping:`${r} bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300`,completed:`${r} bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300`,cancelled:`${r} bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300`}[s]||`${r} bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300`},z=s=>({pending:"bg-yellow-400",confirmed:"bg-blue-400",shipping:"bg-purple-400",completed:"bg-green-400",cancelled:"bg-red-400"})[s]||"bg-gray-400",A=s=>{const r="text-sm font-medium";return{pending:`${r} text-yellow-600 dark:text-yellow-400`,partial:`${r} text-blue-600 dark:text-blue-400`,paid:`${r} text-green-600 dark:text-green-400`,cancelled:`${r} text-red-600 dark:text-red-400`}[s]||`${r} text-gray-600 dark:text-gray-400`},c=s=>({pending:"Chờ thanh toán",partial:"Thanh toán một phần",paid:"Đã thanh toán",cancelled:"Đã hủy"})[s]||"Không xác định",F=s=>{const r="px-2 inline-flex text-xs leading-5 font-semibold rounded-full";return s==="product"?`${r} bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-400`:`${r} bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-400`},J=V(()=>["pending","confirmed"].includes(t.order.status)),Q=V(()=>t.order.invoice&&!["paid","cancelled"].includes(t.order.invoice.status)&&t.order.status!=="cancelled"),Z=V(()=>!t.order.invoice&&t.order.status!=="cancelled"&&t.order.status!=="completed"),R=V(()=>{var s;return["confirmed","shipping"].includes(t.order.status)&&((s=t.order.invoice)==null?void 0:s.status)==="paid"}),q=V(()=>t.order.order_items.some(s=>s.item_type==="service"&&["combo_5","combo_10"].includes(s.service_type))),W=async()=>{var s,r;k.value=!0;try{(await j.put(route("orders.update",t.order.id),{status:y.value,note:b.value})).data.success&&(l.success("Cập nhật trạng thái thành công"),I.reload())}catch(d){console.error("Error updating order status:",d),l.error(((r=(s=d.response)==null?void 0:s.data)==null?void 0:r.message)||"Có lỗi xảy ra khi cập nhật trạng thái")}finally{k.value=!1,n.value=!1}},Y=async s=>{try{await j.post(`/api/invoices/${t.order.invoice.id}/payments`,s),a.value=!1}catch(r){console.error("Error processing payment:",r)}},ee=async()=>{var s,r;k.value=!0;try{const d=await j.post(`/api/orders/${t.order.id}/create-invoice`,{note:i.value});if(d.data.success)l.success("Hóa đơn đã được tạo thành công"),I.visit(route("invoices.show",d.data.data.id));else throw new Error(d.data.message)}catch(d){console.error("Error creating invoice:",d),l.error(((r=(s=d.response)==null?void 0:s.data)==null?void 0:r.message)||"Có lỗi xảy ra khi tạo hóa đơn")}finally{k.value=!1,o.value=!1,i.value=""}},X=s=>({pending:"Chờ xác nhận",confirmed:"Đã xác nhận",shipping:"Đang giao hàng",completed:"Hoàn thành",cancelled:"Đã hủy"})[s]||"Không xác định",te=()=>{t.order.invoice&&I.visit(route("invoices.show",t.order.invoice.id))},ne=()=>{t.order.invoice&&I.visit(route("invoices.show",t.order.invoice.id))},se=()=>{o.value=!0},oe=s=>({single:"Đơn lẻ",combo_5:"Combo 5 lần",combo_10:"Combo 10 lần"})[s]||s,ae=()=>{y.value=t.order.status,b.value=t.order.note||"",n.value=!0},re=()=>{p.value=!0,m.value=""},le=()=>{f.value=!0,h.value=""},de=async()=>{var s,r;k.value=!0;try{(await j.post(`/api/orders/${t.order.id}/complete`,{note:m.value})).data.success&&(l.success("Đơn hàng đã được hoàn thành thành công"),q.value&&l.info("Các gói liệu trình đã được tạo cho khách hàng"),I.reload())}catch(d){console.error("Error completing order:",d),l.error(((r=(s=d.response)==null?void 0:s.data)==null?void 0:r.message)||"Có lỗi xảy ra khi hoàn thành đơn hàng")}finally{k.value=!1,p.value=!1}},ie=async()=>{var s,r;k.value=!0;try{(await j.post(`/api/orders/${t.order.id}/cancel`,{note:h.value})).data.success&&(l.success("Đơn h��ng đã được hủy thành công"),I.reload())}catch(d){console.error("Error canceling order:",d),l.error(((r=(s=d.response)==null?void 0:s.data)==null?void 0:r.message)||"Có lỗi xảy ra khi hủy đơn hàng")}finally{k.value=!1,f.value=!1}},ce=V(()=>({pending:["confirmed","cancelled"],confirmed:["shipping","completed","cancelled"],shipping:["completed","cancelled"],completed:[],cancelled:[]})[t.order.status]||[]),ue=V(()=>{var r;const s=[];return s.push({id:"created",content:"Đơn hàng được tạo",datetime:t.order.created_at,iconClass:"mdi mdi-plus-circle",iconBackground:"bg-blue-500"}),(r=t.order.status_histories)!=null&&r.length&&t.order.status_histories.forEach(d=>{s.push({id:d.id,content:`Trạng thái đơn hàng thay đổi thành "${X(d.status)}"${d.note?` - ${d.note}`:""}`,datetime:d.created_at,iconClass:"mdi mdi-clipboard-check",iconBackground:"bg-green-500"})}),t.order.status==="completed"?s.push({id:"completed",content:"Đơn hàng hoàn thành",datetime:t.order.completed_at||t.order.updated_at,iconClass:"mdi mdi-check-circle",iconBackground:"bg-green-600"}):t.order.status==="cancelled"&&s.push({id:"cancelled",content:"Đơn hàng đã hủy",datetime:t.order.cancelled_at||t.order.updated_at,iconClass:"mdi mdi-close-circle",iconBackground:"bg-red-600"}),s.sort((d,S)=>new Date(S.datetime)-new Date(d.datetime))}),me=s=>new Date(s).toLocaleString("vi-VN",{year:"numeric",month:"long",day:"numeric",hour:"2-digit",minute:"2-digit"}),ge=async s=>{var r,d;k.value=!0;try{const S=await j.put(route("orders.update",t.order.id),{status:s.status,note:s.note});if(S.data.success)l.success("Cập nhật trạng thái thành công"),I.reload();else throw new Error(S.data.message||"Có lỗi xảy ra")}catch(S){console.error("Error updating order status:",S),l.error(((d=(r=S.response)==null?void 0:r.data)==null?void 0:d.message)||"Có lỗi xảy ra khi cập nhật trạng thái")}finally{k.value=!1,n.value=!1}},he=async s=>{var r,d,S;k.value=!0;try{const U=await j.post(`/api/orders/${t.order.id}/create-invoice`,{note:s.note});if(U.data.success)l.success("Tạo hóa đơn thành công"),(r=U.data.data)!=null&&r.id?I.visit(route("invoices.show",U.data.data.id)):I.reload();else throw new Error(U.data.message||"Có lỗi xảy ra")}catch(U){console.error("Error creating invoice:",U),l.error(((S=(d=U.response)==null?void 0:d.data)==null?void 0:S.message)||"Có lỗi xảy ra khi tạo hóa đơn")}finally{k.value=!1,o.value=!1}},pe=()=>{l.success("Đơn hàng đã được hoàn thành thành công"),q.value&&l.info("Các gói liệu trình đã được tạo cho khách hàng"),I.reload()},fe=()=>{I.reload()},ve=s=>{var r,d;return((r=s.product)==null?void 0:r.name)||((d=s.service)==null?void 0:d.service_name)||"N/A"},ye=s=>{var r,d;return((r=s.product)==null?void 0:r.image)||((d=s.service)==null?void 0:d.image)||null},be=V(()=>["pending","confirmed"].includes(t.order.status)&&(!t.order.invoice||t.order.invoice.status==="pending"));H(()=>n.value,s=>{s||(b.value="",y.value=t.order.status)}),H(()=>f.value,s=>{s||(h.value="")});const xe=s=>s?`${s.ward}, ${s.district}, ${s.province}`:"",ke=V(()=>{var d;return!t.order.invoice||t.order.invoice.status!=="paid"&&t.order.status!=="completed"||t.order.order_items.filter(S=>S.item_type==="service"&&["combo_5","combo_10"].includes(S.service_type)).length===0?!1:!((d=t.order.user_service_packages)==null?void 0:d.some(S=>S.order_id===t.order.id))});return{showStatusModal:n,showPaymentModal:a,showCreateInvoiceModal:o,showCompleteModal:p,showCancelModal:f,newStatus:y,statusNote:b,invoiceNote:i,completeNote:m,cancelNote:h,formatCurrency:P,formatDate:L,getStatusClass:$,getStatusDotClass:z,getPaymentStatusClass:A,getPaymentStatusText:c,getItemTypeClass:F,canUpdateStatus:J,canProcessPayment:Q,canCreateInvoice:Z,canComplete:R,hasServiceCombo:q,updateOrderStatus:W,processPayment:Y,createInvoice:ee,getStatusText:X,goToInvoice:te,openPaymentModal:ne,openCreateInvoiceModal:se,getServiceTypeText:oe,loading:k,openUpdateStatusModal:ae,getAvailableStatuses:ce,openCompleteModal:re,openCancelModal:le,completeOrder:de,cancelOrder:ie,orderTimeline:ue,formatDateTime:me,handleStatusUpdated:ge,handleInvoiceCreated:he,handleOrderCompleted:pe,handleOrderCancelled:fe,getItemName:ve,getItemImage:ye,canCancel:be,formatAddress:xe,needsServicePackageCreation:ke}}},kt={class:"grid grid-cols-1 lg:grid-cols-3 gap-6"},_t={class:"lg:col-span-2 space-y-6"},Ct={class:"flex justify-between items-center"},wt={class:"text-lg font-medium"},St={class:"grid grid-cols-2 gap-4"},Tt={class:"space-y-2"},Vt={class:"space-y-2"},Mt={key:0},It={class:"mt-4 pt-4 border-t dark:border-dark-border"},Bt={key:0,class:"bg-gray-50 dark:bg-dark-bg/50 p-3 rounded-lg"},jt={class:"font-medium"},Ut={class:"text-gray-600 dark:text-gray-400"},Nt={key:1,class:"text-gray-500 text-sm"},Ot={class:"overflow-x-auto"},Ht={class:"min-w-full divide-y dark:divide-dark-border"},Dt={class:"divide-y dark:divide-dark-border"},Et={class:"px-4 py-4"},Pt={class:"flex items-center"},Lt=["src"],$t={class:"font-medium"},zt={class:"text-sm text-gray-500"},At={key:0,class:"ml-2"},Ft={class:"px-4 py-4"},qt={class:"px-4 py-4"},Gt={class:"px-4 py-4 font-medium"},Kt={class:"bg-gray-50 dark:bg-dark-bg/50"},Xt={class:"px-4 py-3 font-medium"},Jt={key:0},Qt={class:"px-4 py-3 font-medium text-green-600"},Zt={class:"border-t dark:border-dark-border"},Rt={class:"px-4 py-3 font-medium text-lg"},Wt={class:"flow-root"},Yt={role:"list",class:"-mb-8"},en={class:"relative pb-8"},tn={key:0,class:"absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-dark-border","aria-hidden":"true"},nn={class:"relative flex space-x-3"},sn={class:"flex min-w-0 flex-1 justify-between space-x-4 pt-1.5"},on={class:"text-sm text-gray-500"},an={class:"whitespace-nowrap text-right text-sm text-gray-500"},rn=["datetime"],ln={class:"lg:col-span-1 space-y-6"},dn={class:"space-y-4"},cn={class:"flex justify-between items-center"},un={class:"space-y-3 pt-3 border-t dark:border-dark-border"},mn={key:0,class:"space-y-4"},gn={class:"flex justify-between items-center"},hn={class:"space-y-2"},pn={class:"flex justify-between"},fn={class:"font-medium"},vn={class:"flex justify-between"},yn={class:"text-green-600 font-medium"},bn={class:"flex justify-between"},xn={class:"text-red-600 font-medium"},kn={key:1,class:"text-center py-6"};function _n(t,n,a,o,p,f){const y=M("Head"),b=M("StatusBadge"),i=M("CardBox"),m=M("ItemTypeBadge"),h=M("PaymentStatusBadge"),l=M("Link"),k=M("UpdateStatusModal"),P=M("CreateInvoiceModal"),L=M("CompleteOrderModal"),$=M("CancelOrderModal"),z=M("SectionMain"),A=M("LayoutAuthenticated");return g(),N(A,null,{default:C(()=>[T(y,{title:`Chi tiết đơn hàng #${a.order.id}`},null,8,["title"]),T(z,{breadcrumbs:[{label:"Quản lý đơn hàng",href:t.route("orders.index")},{label:`Đơn hàng #${a.order.id}`}]},{default:C(()=>[e("div",kt,[e("div",_t,[T(i,null,{header:C(()=>[e("div",Ct,[e("h3",wt,"Thông tin đơn hàng #"+u(a.order.id),1),T(b,{status:a.order.status},null,8,["status"])])]),default:C(()=>{var c;return[e("div",St,[e("div",null,[n[11]||(n[11]=e("h4",{class:"font-medium mb-2"},"Thông tin khách hàng",-1)),e("div",Tt,[e("p",null,[n[8]||(n[8]=e("span",{class:"text-gray-500"},"Họ tên:",-1)),_(" "+u(a.order.user.full_name),1)]),e("p",null,[n[9]||(n[9]=e("span",{class:"text-gray-500"},"Email:",-1)),_(" "+u(a.order.user.email),1)]),e("p",null,[n[10]||(n[10]=e("span",{class:"text-gray-500"},"Điện thoại:",-1)),_(" "+u(a.order.user.phone_number),1)])])]),e("div",null,[n[15]||(n[15]=e("h4",{class:"font-medium mb-2"},"Thông tin đặt hàng",-1)),e("div",Vt,[e("p",null,[n[12]||(n[12]=e("span",{class:"text-gray-500"},"Ngày đặt:",-1)),_(" "+u(o.formatDateTime(a.order.created_at)),1)]),e("p",null,[n[13]||(n[13]=e("span",{class:"text-gray-500"},"Phương thức:",-1)),_(" "+u((c=a.order.payment_method)==null?void 0:c.method_name),1)]),a.order.voucher?(g(),v("p",Mt,[n[14]||(n[14]=e("span",{class:"text-gray-500"},"Mã giảm giá:",-1)),_(" "+u(a.order.voucher.code),1)])):w("",!0)])])]),e("div",It,[n[16]||(n[16]=e("h4",{class:"font-medium mb-2"},"Địa chỉ giao hàng",-1)),a.order.shipping_address?(g(),v("div",Bt,[e("p",jt,u(a.order.shipping_address.address),1),e("p",Ut,u(o.formatAddress(a.order.shipping_address))+" ("+u(a.order.shipping_address.ward.name)+", "+u(a.order.shipping_address.district.name)+", "+u(a.order.shipping_address.province.name)+") ",1)])):(g(),v("p",Nt,"Chưa có địa chỉ giao hàng"))])]}),_:1}),T(i,null,{header:C(()=>n[17]||(n[17]=[e("h3",{class:"text-lg font-medium"},"Danh sách sản phẩm/dịch vụ",-1)])),default:C(()=>[e("div",Ot,[e("table",Ht,[n[21]||(n[21]=e("thead",null,[e("tr",{class:"bg-gray-50 dark:bg-dark-bg/50"},[e("th",{class:"px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"}," Sản phẩm/Dịch vụ "),e("th",{class:"px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"}," Đơn giá "),e("th",{class:"px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"}," Số lượng "),e("th",{class:"px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"}," Thành tiền ")])],-1)),e("tbody",Dt,[(g(!0),v(G,null,K(a.order.order_items,c=>(g(),v("tr",{key:c.id,class:"hover:bg-gray-50 dark:hover:bg-dark-bg/30"},[e("td",Et,[e("div",Pt,[o.getItemImage(c)?(g(),v("img",{key:0,src:o.getItemImage(c),class:"h-10 w-10 rounded-lg object-cover mr-3"},null,8,Lt)):w("",!0),e("div",null,[e("div",$t,u(o.getItemName(c)),1),e("div",zt,[T(m,{type:c.item_type},null,8,["type"]),c.service_type?(g(),v("span",At,u(o.getServiceTypeText(c.service_type)),1)):w("",!0)])])])]),e("td",Ft,u(o.formatCurrency(c.price)),1),e("td",qt,u(c.quantity),1),e("td",Gt,u(o.formatCurrency(c.price*c.quantity)),1)]))),128))]),e("tfoot",Kt,[e("tr",null,[n[18]||(n[18]=e("td",{colspan:"3",class:"px-4 py-3 text-right font-medium"},"Tổng tiền:",-1)),e("td",Xt,u(o.formatCurrency(a.order.total_amount)),1)]),a.order.discount_amount?(g(),v("tr",Jt,[n[19]||(n[19]=e("td",{colspan:"3",class:"px-4 py-3 text-right font-medium"},"Giảm giá:",-1)),e("td",Qt," -"+u(o.formatCurrency(a.order.discount_amount)),1)])):w("",!0),e("tr",Zt,[n[20]||(n[20]=e("td",{colspan:"3",class:"px-4 py-3 text-right font-medium"},"Thành tiền:",-1)),e("td",Rt,u(o.formatCurrency(a.order.total_amount-a.order.discount_amount)),1)])])])])]),_:1}),o.orderTimeline.length?(g(),N(i,{key:0},{header:C(()=>n[22]||(n[22]=[e("h3",{class:"text-lg font-medium"},"Lịch sử đơn hàng",-1)])),default:C(()=>[e("div",Wt,[e("ul",Yt,[(g(!0),v(G,null,K(o.orderTimeline,(c,F)=>(g(),v("li",{key:c.id},[e("div",en,[F!==o.orderTimeline.length-1?(g(),v("span",tn)):w("",!0),e("div",nn,[e("div",null,[e("span",{class:B(["h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-dark-surface",c.iconBackground])},[e("i",{class:B([c.iconClass,"text-white"]),"aria-hidden":"true"},null,2)],2)]),e("div",sn,[e("div",null,[e("p",on,u(c.content),1)]),e("div",an,[e("time",{datetime:c.datetime},u(o.formatDateTime(c.datetime)),9,rn)])])])])]))),128))])])]),_:1})):w("",!0)]),e("div",ln,[T(i,null,{default:C(()=>[e("div",dn,[e("div",null,[n[24]||(n[24]=e("h3",{class:"text-lg font-medium mb-3"},"Trạng thái đơn hàng",-1)),e("div",cn,[n[23]||(n[23]=e("span",{class:"text-gray-600"},"Trạng thái hiện tại",-1)),T(b,{status:a.order.status,class:"px-3 py-1"},null,8,["status"])])]),e("div",un,[o.canUpdateStatus?(g(),v("button",{key:0,onClick:n[0]||(n[0]=(...c)=>o.openUpdateStatusModal&&o.openUpdateStatusModal(...c)),class:"w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg font-medium flex items-center justify-center transition-colors duration-200"},n[25]||(n[25]=[e("i",{class:"mdi mdi-pencil-outline mr-2"},null,-1),_(" Cập nhật trạng thái ")]))):w("",!0),o.canCancel?(g(),v("button",{key:1,onClick:n[1]||(n[1]=(...c)=>o.openCancelModal&&o.openCancelModal(...c)),class:"w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg font-medium flex items-center justify-center transition-colors duration-200"},n[26]||(n[26]=[e("i",{class:"mdi mdi-close-circle-outline mr-2"},null,-1),_(" Hủy đơn hàng ")]))):w("",!0),o.needsServicePackageCreation?(g(),v("button",{key:2,onClick:n[2]||(n[2]=(...c)=>o.openCompleteModal&&o.openCompleteModal(...c)),class:"w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-medium flex items-center justify-center transition-colors duration-200"},[n[27]||(n[27]=e("i",{class:"mdi mdi-check-circle-outline mr-2"},null,-1)),_(" "+u(a.order.status==="completed"?"Tạo gói dịch vụ":"Hoàn thành đơn hàng"),1)])):w("",!0)])])]),_:1}),T(i,null,{header:C(()=>n[28]||(n[28]=[e("h3",{class:"text-lg font-medium"},"Thông tin thanh toán",-1)])),default:C(()=>[a.order.invoice?(g(),v("div",mn,[e("div",gn,[n[29]||(n[29]=e("span",{class:"text-gray-500"},"Trạng thái",-1)),T(h,{status:a.order.invoice.status},null,8,["status"])]),e("div",hn,[e("div",pn,[n[30]||(n[30]=e("span",{class:"text-gray-500"},"Tổng tiền",-1)),e("span",fn,u(o.formatCurrency(a.order.invoice.total_amount)),1)]),e("div",vn,[n[31]||(n[31]=e("span",{class:"text-gray-500"},"Đã thanh toán",-1)),e("span",yn,u(o.formatCurrency(a.order.invoice.paid_amount)),1)]),e("div",bn,[n[32]||(n[32]=e("span",{class:"text-gray-500"},"Còn lại",-1)),e("span",xn,u(o.formatCurrency(a.order.invoice.remaining_amount)),1)])]),T(l,{href:t.route("invoices.show",a.order.invoice.id),class:"btn-primary w-full text-center"},{default:C(()=>n[33]||(n[33]=[e("i",{class:"mdi mdi-file-document-outline mr-2"},null,-1),_(" Xem chi tiết hóa đơn ")])),_:1},8,["href"])])):(g(),v("div",kn,[n[35]||(n[35]=e("div",{class:"mb-4"},[e("i",{class:"mdi mdi-file-document-plus-outline text-5xl text-gray-400"})],-1)),n[36]||(n[36]=e("p",{class:"text-gray-500 mb-4"},"Chưa có hóa đơn cho đơn hàng này",-1)),o.canCreateInvoice?(g(),v("button",{key:0,onClick:n[3]||(n[3]=(...c)=>o.openCreateInvoiceModal&&o.openCreateInvoiceModal(...c)),class:"w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center transition-colors duration-200"},n[34]||(n[34]=[e("i",{class:"mdi mdi-plus-circle-outline mr-2 text-xl"},null,-1),_(" Tạo hóa đơn mới ")]))):w("",!0)]))]),_:1})])]),T(k,{modelValue:o.showStatusModal,"onUpdate:modelValue":n[4]||(n[4]=c=>o.showStatusModal=c),order:a.order,"available-statuses":o.getAvailableStatuses,onUpdated:o.handleStatusUpdated},null,8,["modelValue","order","available-statuses","onUpdated"]),T(P,{modelValue:o.showCreateInvoiceModal,"onUpdate:modelValue":n[5]||(n[5]=c=>o.showCreateInvoiceModal=c),order:a.order,onCreated:o.handleInvoiceCreated},null,8,["modelValue","order","onCreated"]),T(L,{modelValue:o.showCompleteModal,"onUpdate:modelValue":n[6]||(n[6]=c=>o.showCompleteModal=c),order:a.order,"has-service-combo":o.hasServiceCombo,onCompleted:o.handleOrderCompleted},null,8,["modelValue","order","has-service-combo","onCompleted"]),T($,{modelValue:o.showCancelModal,"onUpdate:modelValue":n[7]||(n[7]=c=>o.showCancelModal=c),order:a.order,onCancelled:o.handleOrderCancelled},null,8,["modelValue","order","onCancelled"])]),_:1},8,["breadcrumbs"])]),_:1})}const hs=Be(xt,[["render",_n]]);export{hs as default};