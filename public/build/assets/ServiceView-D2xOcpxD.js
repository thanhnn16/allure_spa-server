import{r as j,e as H,aU as l,a0 as D,bH as b,aa as i,bl as a,a3 as e,a1 as v,a2 as n,bJ as _,bz as N,F as z,b0 as B,bf as f,bA as O,j as h}from"./@vue-BRgAy5zV.js";import{az as G,b as J,F as K,H as A,aA as Q,aB as Z,aC as q}from"./@mdi-D7koDRFv.js";import{_ as R}from"./SectionMain-Vot1TOkK.js";import{_ as W}from"./CardBox-jfYP3QDG.js";import{L as Y}from"./LayoutAuthenticated-efRhzLqt.js";import{_ as ee}from"./SectionTitleLineWithButton-Dd18eFV5.js";import{_ as m}from"./BaseButton-eMTGofBM.js";import{_ as x}from"./BaseIcon-B4kwkOCn.js";import{T as re,Z as te,F as P}from"./@inertiajs-D0DMCEQB.js";import{_ as se}from"./TablePagination-BLryTwgf.js";import{_ as ae}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./app-CCOuM6Nc.js";import"./jspdf-Ciebq7We.js";import"./call-bind-aBC2DkHY.js";import"./get-intrinsic-BKEvijrG.js";import"./es-errors-DzOT6E3C.js";import"./has-symbols-eVqrYdw7.js";import"./has-proto-JnoBQRdH.js";import"./function-bind-BbkWVFrm.js";import"./hasown-DYqjtFKE.js";import"./set-function-length-B5OANX0j.js";import"./define-data-property-DO9o5wXF.js";import"./es-define-property-tzmkNPou.js";import"./gopd-CEkvUycD.js";import"./has-property-descriptors-DphFXkuo.js";import"./@babel-BsCcpEdk.js";import"./fflate-EnPhvkmS.js";import"./axios-CCb-kr4I.js";import"./laravel-echo-C9ppFO4I.js";import"./pusher-js-Cl-1XqxI.js";import"./laravel-vite-plugin-DEL3ZhID.js";import"./pinia-BhnhDyRZ.js";import"./vue-toastification-ejyjJRED.js";import"./firebase-DL3QX-oU.js";import"./@firebase--zA4YiqN.js";import"./idb-Bn1DMRyg.js";import"./deepmerge-CtOfIP5S.js";import"./qs-Bnt7aRCy.js";import"./side-channel-FJR906QQ.js";import"./object-inspect-D3SFxae_.js";import"./nprogress-uqLJ5xmn.js";import"./lodash.clonedeep-Bxvn-V0B.js";import"./lodash.isequal-BYpQg7Um.js";import"./colors-S7_1wET5.js";import"./OverlayLayer-BSs7OF78.js";const oe={class:"flex items-center justify-between mb-3"},ie={class:"flex items-center space-x-4"},le={key:0,class:"mb-4"},ne={class:"grid grid-cols-2 gap-4 mb-4"},ce=["value"],de={class:"my-4"},me={key:1,class:"text-center py-4 text-gray-500 dark:text-gray-400"},pe={key:2,class:"overflow-x-auto"},ue={class:"w-full text-sm text-gray-500 dark:text-gray-300"},fe={class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-dark-surface/50 dark:text-gray-300"},ge={class:"flex items-center justify-between h-full"},ye={class:"flex items-center justify-between h-full"},ke={class:"flex items-center justify-between h-full"},be={class:"flex items-center justify-between h-full"},he={class:"px-6 py-4"},xe=["src","alt"],ve={class:"px-6 py-4 font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap"},_e={class:"px-6 py-4"},we={class:"px-6 py-4"},Ce={class:"px-6 py-4"},$e={class:"px-6 py-4"},Fe={key:0,class:"mt-6"},Te={__name:"ServiceView",props:{services:Object,categories:Array,filters:Object},setup(c){var C,$,F,T,V;const p=c,g=j(!1),s=re({search:((C=p.filters)==null?void 0:C.search)||"",category:(($=p.filters)==null?void 0:$.category)||"",per_page:((F=p.filters)==null?void 0:F.per_page)||10,sort:((T=p.filters)==null?void 0:T.sort)||"",direction:((V=p.filters)==null?void 0:V.direction)||"asc"}),w=j(null);H(()=>s.search,o=>{clearTimeout(w.value),w.value=setTimeout(()=>{u()},300)});const U=()=>{g.value=!g.value},u=()=>{P.get(route("services.index"),s,{preserveState:!0})},X=()=>{s.reset(),u()},I=()=>{u()},y=o=>{s.sort===o?s.direction=s.direction==="asc"?"desc":"asc":(s.sort=o,s.direction="asc"),u()},k=o=>s.sort!==o?Q:s.direction==="asc"?Z:q,L=o=>new Intl.NumberFormat("vi-VN",{style:"currency",currency:"VND"}).format(o),M=o=>{if(o==null)return"Chưa cập nhật";const r=Math.floor(o/60),t=o%60;let d="";return r>0&&(d+=`${r}h `),(t>0||r===0)&&(d+=`${t}m`),d.trim()},E=o=>{P.visit(route("services.show",o))};return(o,r)=>(l(),D(Y,null,{default:b(()=>[i(a(te),{title:"Quản lý liệu trình"}),i(R,null,{default:b(()=>[i(ee,{icon:a(G),title:"Danh sách liệu trình",main:""},{default:b(()=>[i(m,{icon:a(J),label:"Tạo liệu trình",color:"info","rounded-full":"",small:""},null,8,["icon"]),i(m,{icon:a(K),label:"Nhập từ Excel",color:"success","rounded-full":"",small:""},null,8,["icon"])]),_:1},8,["icon"]),i(W,{class:"mb-6 px-4 py-4 dark:bg-dark-surface","has-table":""},{default:b(()=>[e("div",oe,[e("div",ie,[i(m,{icon:a(A),label:"Bộ lọc",onClick:U},null,8,["icon"]),g.value?(l(),D(m,{key:0,label:"Đặt lại bộ lọc",onClick:X})):v("",!0)])]),g.value?(l(),n("div",le,[e("div",ne,[e("div",null,[r[8]||(r[8]=e("label",{class:"block mb-2 text-gray-700 dark:text-gray-300"},"Danh mục",-1)),_(e("select",{"onUpdate:modelValue":r[0]||(r[0]=t=>a(s).category=t),class:"w-full px-4 py-2 border rounded-md bg-white dark:bg-dark-surface dark:border-dark-border dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-500"},[r[7]||(r[7]=e("option",{value:"",class:"dark:bg-dark-surface"},"Tất cả danh mục",-1)),(l(!0),n(z,null,B(c.categories,t=>(l(),n("option",{key:t.id,value:t.id,class:"dark:bg-dark-surface"},f(t.service_category_name),9,ce))),128))],512),[[N,a(s).category]])])]),i(m,{icon:a(A),label:"Áp dụng bộ lọc",onClick:u},null,8,["icon"])])):v("",!0),e("div",de,[_(e("input",{"onUpdate:modelValue":r[1]||(r[1]=t=>a(s).search=t),type:"text",placeholder:"Tìm kiếm liệu trình...",class:"w-full px-4 py-2 mb-4 border rounded-md bg-white dark:bg-dark-surface dark:border-dark-border dark:text-gray-300 dark:placeholder-gray-500 focus:border-primary-500 dark:focus:border-primary-500"},null,512),[[O,a(s).search]]),_(e("select",{"onUpdate:modelValue":r[2]||(r[2]=t=>a(s).per_page=t),onChange:I,class:"px-8 py-2 border rounded-md bg-white dark:bg-dark-surface dark:border-dark-border dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-500"},r[9]||(r[9]=[e("option",{value:10,class:"dark:bg-dark-surface"},"Xem 10 mỗi trang",-1),e("option",{value:25,class:"dark:bg-dark-surface"},"Xem 25 mỗi trang",-1),e("option",{value:50,class:"dark:bg-dark-surface"},"Xem 50 mỗi trang",-1)]),544),[[N,a(s).per_page]])]),!c.services.data||c.services.data.length===0?(l(),n("div",me," Không có dữ liệu liệu trình ")):(l(),n("div",pe,[e("table",ue,[e("thead",fe,[e("tr",null,[r[14]||(r[14]=e("th",{scope:"col",class:"px-6 py-3 w-24"},"Ảnh",-1)),e("th",{onClick:r[3]||(r[3]=t=>y("service_name")),scope:"col",class:"px-6 py-3 cursor-pointer"},[e("div",ge,[r[10]||(r[10]=e("span",{class:"mr-2"},"Tên liệu trình",-1)),i(x,{path:k("service_name"),size:"18",class:h(["flex-shrink-0",{"text-primary-600 dark:text-primary-400":a(s).sort==="service_name","text-gray-400 dark:text-gray-600":a(s).sort!=="service_name"}])},null,8,["path","class"])])]),e("th",{onClick:r[4]||(r[4]=t=>y("category_id")),scope:"col",class:"px-6 py-3 cursor-pointer"},[e("div",ye,[r[11]||(r[11]=e("span",{class:"mr-2"},"Danh mục",-1)),i(x,{path:k("category_id"),size:"18",class:h(["flex-shrink-0",{"text-gray-900":a(s).sort==="category_id","text-gray-400":a(s).sort!=="category_id"}])},null,8,["path","class"])])]),e("th",{onClick:r[5]||(r[5]=t=>y("duration")),scope:"col",class:"px-6 py-3 cursor-pointer"},[e("div",ke,[r[12]||(r[12]=e("span",{class:"mr-2"},"Thời gian",-1)),i(x,{path:k("duration"),size:"18",class:h(["flex-shrink-0",{"text-gray-900":a(s).sort==="duration","text-gray-400":a(s).sort!=="duration"}])},null,8,["path","class"])])]),e("th",{onClick:r[6]||(r[6]=t=>y("single_price")),scope:"col",class:"px-6 py-3 cursor-pointer"},[e("div",be,[r[13]||(r[13]=e("span",{class:"mr-2"},"Giá (1 lần)",-1)),i(x,{path:k("single_price"),size:"18",class:h(["flex-shrink-0",{"text-gray-900":a(s).sort==="single_price","text-gray-400":a(s).sort!=="single_price"}])},null,8,["path","class"])])]),r[15]||(r[15]=e("th",{scope:"col",class:"px-6 py-3 w-32"},"Thao tác",-1))])]),e("tbody",null,[(l(!0),n(z,null,B(c.services.data,t=>{var d,S;return l(),n("tr",{key:t.id,class:"bg-white border-b hover:bg-gray-50 dark:bg-dark-surface dark:border-dark-border dark:hover:bg-dark-surface/70"},[e("td",he,[e("img",{src:((d=t.image)==null?void 0:d.url)||"https://via.placeholder.com/150",alt:t.service_name,class:"w-16 h-16 object-cover rounded-md"},null,8,xe)]),e("td",ve,f(t.service_name),1),e("td",_e,f(((S=t.category)==null?void 0:S.service_category_name)||"N/A"),1),e("td",we,f(M(t.duration)),1),e("td",Ce,f(L(t.single_price)),1),e("td",$e,[i(m,{label:"Xem chi tiết",color:"info",small:"",onClick:Ve=>E(t.id)},null,8,["onClick"])])])}),128))])])]))]),_:1}),c.services.links?(l(),n("div",Fe,[i(se,{links:c.services.links},null,8,["links"])])):v("",!0)]),_:1})]),_:1}))}},xr=ae(Te,[["__scopeId","data-v-336693ff"]]);export{xr as default};