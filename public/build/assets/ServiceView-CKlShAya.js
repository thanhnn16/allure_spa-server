import{r as j,e as G,aU as a,a0 as D,bH as v,aa as l,bl as r,a3 as t,a1 as b,a2 as n,bJ as k,bz as N,F as z,b0 as B,bf as f,bA as O,j as _}from"./@vue-BRgAy5zV.js";import{aw as H,b as J,G as K,I as A,ax as Q,ay as R,az as Z}from"./@mdi-CTfBXXzV.js";import{_ as q}from"./SectionMain-BbIPmLtw.js";import{_ as W}from"./CardBox-jfYP3QDG.js";import{L as Y}from"./LayoutAuthenticated-XZirOlYW.js";import{_ as tt}from"./SectionTitleLineWithButton-O7fqc9DU.js";import{_ as d}from"./BaseButton-eMTGofBM.js";import{_ as x}from"./BaseIcon-B4kwkOCn.js";import{T as et,Z as st,F as P}from"./@inertiajs-D0DMCEQB.js";import{_ as ot}from"./TablePagination-BLryTwgf.js";import{_ as rt}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./app-sTdmxqRJ.js";import"./jspdf-Ciebq7We.js";import"./call-bind-aBC2DkHY.js";import"./get-intrinsic-BKEvijrG.js";import"./es-errors-DzOT6E3C.js";import"./has-symbols-eVqrYdw7.js";import"./has-proto-JnoBQRdH.js";import"./function-bind-BbkWVFrm.js";import"./hasown-DYqjtFKE.js";import"./set-function-length-B5OANX0j.js";import"./define-data-property-DO9o5wXF.js";import"./es-define-property-tzmkNPou.js";import"./gopd-CEkvUycD.js";import"./has-property-descriptors-DphFXkuo.js";import"./@babel-BsCcpEdk.js";import"./fflate-EnPhvkmS.js";import"./axios-CCb-kr4I.js";import"./laravel-echo-C9ppFO4I.js";import"./pusher-js-Cl-1XqxI.js";import"./laravel-vite-plugin-DEL3ZhID.js";import"./pinia-BhnhDyRZ.js";import"./vue-toastification-ejyjJRED.js";import"./firebase-DL3QX-oU.js";import"./@firebase--zA4YiqN.js";import"./idb-Bn1DMRyg.js";import"./deepmerge-CtOfIP5S.js";import"./qs-Bnt7aRCy.js";import"./side-channel-FJR906QQ.js";import"./object-inspect-D3SFxae_.js";import"./nprogress-uqLJ5xmn.js";import"./lodash.clonedeep-Bxvn-V0B.js";import"./lodash.isequal-BYpQg7Um.js";import"./colors-S7_1wET5.js";import"./OverlayLayer-BSs7OF78.js";const it={class:"flex items-center justify-between mb-3"},lt={class:"flex items-center space-x-4"},at={key:0,class:"mb-4"},nt={class:"grid grid-cols-2 gap-4 mb-4"},ct=["value"],mt={class:"my-4"},pt={key:1,class:"text-center py-4"},dt={key:2,class:"overflow-x-auto"},ut={class:"w-full text-sm text-left text-gray-500"},ft={class:"text-xs text-gray-700 uppercase bg-gray-50"},yt={class:"flex items-center justify-between h-full"},gt={class:"flex items-center justify-between h-full"},ht={class:"flex items-center justify-between h-full"},vt={class:"flex items-center justify-between h-full"},_t={class:"px-6 py-4"},xt=["src"],bt={class:"px-6 py-4 font-medium text-gray-900 whitespace-nowrap"},kt={class:"px-6 py-4"},wt={class:"px-6 py-4"},Ct={class:"px-6 py-4"},$t={class:"px-6 py-4"},Tt={key:0,class:"mt-6"},Vt={__name:"ServiceView",props:{services:Object,categories:Array,filters:Object},setup(c){var C,$,T,V,F;const m=c;console.log("Received props:",m);const y=j(!1),s=et({search:((C=m.filters)==null?void 0:C.search)||"",category:(($=m.filters)==null?void 0:$.category)||"",per_page:((T=m.filters)==null?void 0:T.per_page)||10,sort:((V=m.filters)==null?void 0:V.sort)||"",direction:((F=m.filters)==null?void 0:F.direction)||"asc"}),w=j(null);G(()=>s.search,i=>{clearTimeout(w.value),w.value=setTimeout(()=>{u()},300)});const I=()=>{y.value=!y.value},u=()=>{P.get(route("services.index"),s,{preserveState:!0})},U=()=>{s.reset(),u()},X=()=>{u()},g=i=>{s.sort===i?s.direction=s.direction==="asc"?"desc":"asc":(s.sort=i,s.direction="asc"),u()},h=i=>s.sort!==i?Q:s.direction==="asc"?R:Z,L=i=>new Intl.NumberFormat("vi-VN",{style:"currency",currency:"VND"}).format(i),M=i=>{if(i==null)return"Chưa cập nhật";const e=Math.floor(i/60),o=i%60;let p="";return e>0&&(p+=`${e}h `),(o>0||e===0)&&(p+=`${o}m`),p.trim()},E=i=>{P.visit(route("services.show",i))};return(i,e)=>(a(),D(Y,null,{default:v(()=>[l(r(st),{title:"Quản lý liệu trình"}),l(q,null,{default:v(()=>[l(tt,{icon:r(H),title:"Danh sách liệu trình",main:""},{default:v(()=>[l(d,{icon:r(J),label:"Tạo liệu trình",color:"info","rounded-full":"",small:""},null,8,["icon"]),l(d,{icon:r(K),label:"Nhập từ Excel",color:"success","rounded-full":"",small:""},null,8,["icon"])]),_:1},8,["icon"]),l(W,{class:"mb-6 px-4 py-4","has-table":""},{default:v(()=>[t("div",it,[t("div",lt,[l(d,{icon:r(A),label:"Bộ lọc",onClick:I},null,8,["icon"]),y.value?(a(),D(d,{key:0,label:"Đặt lại bộ lọc",onClick:U})):b("",!0)])]),y.value?(a(),n("div",at,[t("div",nt,[t("div",null,[e[8]||(e[8]=t("label",{class:"block mb-2"},"Danh mục",-1)),k(t("select",{"onUpdate:modelValue":e[0]||(e[0]=o=>r(s).category=o),class:"w-full px-4 py-2 border rounded-md"},[e[7]||(e[7]=t("option",{value:""},"Tất cả danh mục",-1)),(a(!0),n(z,null,B(c.categories,o=>(a(),n("option",{key:o.id,value:o.id},f(o.service_category_name),9,ct))),128))],512),[[N,r(s).category]])])]),l(d,{icon:r(A),label:"Áp dụng bộ lọc",onClick:u},null,8,["icon"])])):b("",!0),t("div",mt,[k(t("input",{"onUpdate:modelValue":e[1]||(e[1]=o=>r(s).search=o),type:"text",placeholder:"Tìm kiếm sản phẩm...",class:"w-full px-4 py-2 mb-4 border rounded-md"},null,512),[[O,r(s).search]]),k(t("select",{"onUpdate:modelValue":e[2]||(e[2]=o=>r(s).per_page=o),onChange:X,class:"px-8 py-2 border rounded-md"},e[9]||(e[9]=[t("option",{value:10},"Xem 10 mỗi trang",-1),t("option",{value:25},"Xem 25 mỗi trang",-1),t("option",{value:50},"Xem 50 mỗi trang",-1)]),544),[[N,r(s).per_page]])]),!c.services.data||c.services.data.length===0?(a(),n("div",pt," Không có dữ liệu liệu trình ")):(a(),n("div",dt,[t("table",ut,[t("thead",ft,[t("tr",null,[e[14]||(e[14]=t("th",{scope:"col",class:"px-6 py-3 w-24"},"Ảnh",-1)),t("th",{onClick:e[3]||(e[3]=o=>g("service_name")),scope:"col",class:"px-6 py-3 cursor-pointer"},[t("div",yt,[e[10]||(e[10]=t("span",{class:"mr-2"},"Tên liệu trình",-1)),l(x,{path:h("service_name"),size:"18",class:_(["flex-shrink-0",{"text-gray-900":r(s).sort==="service_name","text-gray-400":r(s).sort!=="service_name"}])},null,8,["path","class"])])]),t("th",{onClick:e[4]||(e[4]=o=>g("category_id")),scope:"col",class:"px-6 py-3 cursor-pointer"},[t("div",gt,[e[11]||(e[11]=t("span",{class:"mr-2"},"Danh mục",-1)),l(x,{path:h("category_id"),size:"18",class:_(["flex-shrink-0",{"text-gray-900":r(s).sort==="category_id","text-gray-400":r(s).sort!=="category_id"}])},null,8,["path","class"])])]),t("th",{onClick:e[5]||(e[5]=o=>g("duration")),scope:"col",class:"px-6 py-3 cursor-pointer"},[t("div",ht,[e[12]||(e[12]=t("span",{class:"mr-2"},"Thời gian",-1)),l(x,{path:h("duration"),size:"18",class:_(["flex-shrink-0",{"text-gray-900":r(s).sort==="duration","text-gray-400":r(s).sort!=="duration"}])},null,8,["path","class"])])]),t("th",{onClick:e[6]||(e[6]=o=>g("single_price")),scope:"col",class:"px-6 py-3 cursor-pointer"},[t("div",vt,[e[13]||(e[13]=t("span",{class:"mr-2"},"Giá (1 lần)",-1)),l(x,{path:h("single_price"),size:"18",class:_(["flex-shrink-0",{"text-gray-900":r(s).sort==="single_price","text-gray-400":r(s).sort!=="single_price"}])},null,8,["path","class"])])]),e[15]||(e[15]=t("th",{scope:"col",class:"px-6 py-3 w-32"},"Thao tác",-1))])]),t("tbody",null,[(a(!0),n(z,null,B(c.services.data,o=>{var p,S;return a(),n("tr",{key:o.id,class:"bg-white border-b hover:bg-gray-50"},[t("td",_t,[t("img",{src:((p=o.image)==null?void 0:p.url)||"https://via.placeholder.com/150",alt:"service.name",class:"w-16 h-16 object-cover rounded-md"},null,8,xt)]),t("td",bt,f(o.service_name),1),t("td",kt,f(((S=o.category)==null?void 0:S.service_category_name)||"N/A"),1),t("td",wt,f(M(o.duration)),1),t("td",Ct,f(L(o.single_price)),1),t("td",$t,[l(d,{label:"Xem chi tiết",color:"info",small:"",onClick:Ft=>E(o.id)},null,8,["onClick"])])])}),128))])])]))]),_:1}),c.services.links?(a(),n("div",Tt,[l(ot,{links:c.services.links},null,8,["links"])])):b("",!0)]),_:1})]),_:1}))}},xe=rt(Vt,[["__scopeId","data-v-5ba397fd"]]);export{xe as default};