import{aU as m,a0 as x,bH as r,a3 as i,aa as o,bl as t,a2 as p,bf as u,a1 as n,j as f,bJ as y,bz as c,bM as g}from"./@vue-BRgAy5zV.js";import{T as k}from"./@inertiajs-D0DMCEQB.js";import{_ as N}from"./CardBox-jfYP3QDG.js";import{_ as s}from"./FormField-Da_P6kO8.js";import{_ as d}from"./FormControl-BSsARiYM.js";import{_ as b}from"./BaseButton-eMTGofBM.js";import{_ as w}from"./BaseButtons-B17KSMwc.js";import{j as U}from"./@mdi-DWfqH7fj.js";import"./axios-CCb-kr4I.js";import"./deepmerge-CtOfIP5S.js";import"./call-bind-aBC2DkHY.js";import"./get-intrinsic-BKEvijrG.js";import"./es-errors-DzOT6E3C.js";import"./has-symbols-eVqrYdw7.js";import"./has-proto-JnoBQRdH.js";import"./function-bind-BbkWVFrm.js";import"./hasown-DYqjtFKE.js";import"./set-function-length-B5OANX0j.js";import"./define-data-property-DO9o5wXF.js";import"./es-define-property-tzmkNPou.js";import"./gopd-CEkvUycD.js";import"./has-property-descriptors-DphFXkuo.js";import"./qs-Bnt7aRCy.js";import"./side-channel-FJR906QQ.js";import"./object-inspect-D3SFxae_.js";import"./nprogress-uqLJ5xmn.js";import"./lodash.clonedeep-Bxvn-V0B.js";import"./lodash.isequal-BYpQg7Um.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";import"./main-BNmBi4-k.js";import"./pinia-BhnhDyRZ.js";import"./BaseIcon-B4kwkOCn.js";import"./colors-S7_1wET5.js";const C={key:0,class:"text-red-400"},$={key:0,class:"text-red-400"},S={key:0,class:"text-red-400"},B={key:0,class:"text-red-400"},M={key:0,class:"text-red-400"},ue={__name:"AddStaffModal",props:{modelValue:Boolean},emits:["update:modelValue","staff-added"],setup(V,{emit:h}){const _=h,e=k({full_name:"",phone_number:"",email:"",gender:"",date_of_birth:"",staff_detail:{position:"",department:"",hire_date:""}}),v=()=>{e.post(route("staff.store"),{onSuccess:()=>{e.reset(),_("update:modelValue",!1),_("staff-added")}})};return(z,l)=>V.modelValue?(m(),x(N,{key:0,"is-modal":"",icon:t(U),onClose:l[9]||(l[9]=a=>_("update:modelValue",!1)),title:"Thêm nhân viên mới",class:"shadow-lg w-full md:w-2/3 lg:w-1/2 xl:w-2/5 z-50"},{default:r(()=>[i("form",{onSubmit:g(v,["prevent"])},[o(s,{label:"Họ và tên",class:f({"text-red-400":t(e).errors.full_name})},{default:r(()=>[o(d,{modelValue:t(e).full_name,"onUpdate:modelValue":l[0]||(l[0]=a=>t(e).full_name=a),type:"text",placeholder:"Nhập họ và tên"},null,8,["modelValue"]),t(e).errors.full_name?(m(),p("div",C,u(t(e).errors.full_name),1)):n("",!0)]),_:1},8,["class"]),o(s,{label:"Số điện thoại",class:f({"text-red-400":t(e).errors.phone_number})},{default:r(()=>[o(d,{modelValue:t(e).phone_number,"onUpdate:modelValue":l[1]||(l[1]=a=>t(e).phone_number=a),type:"text",placeholder:"Nhập số điện thoại"},null,8,["modelValue"]),t(e).errors.phone_number?(m(),p("div",$,u(t(e).errors.phone_number),1)):n("",!0)]),_:1},8,["class"]),o(s,{label:"Email",class:f({"text-red-400":t(e).errors.email})},{default:r(()=>[o(d,{modelValue:t(e).email,"onUpdate:modelValue":l[2]||(l[2]=a=>t(e).email=a),type:"email",placeholder:"Nhập email"},null,8,["modelValue"]),t(e).errors.email?(m(),p("div",S,u(t(e).errors.email),1)):n("",!0)]),_:1},8,["class"]),o(s,{label:"Giới tính",class:f({"text-red-400":t(e).errors.gender})},{default:r(()=>[y(i("select",{"onUpdate:modelValue":l[3]||(l[3]=a=>t(e).gender=a),class:"w-full px-4 py-2 border rounded-md dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300"},l[10]||(l[10]=[i("option",{value:""},"Chọn giới tính",-1),i("option",{value:"male"},"Nam",-1),i("option",{value:"female"},"Nữ",-1),i("option",{value:"other"},"Khác",-1)]),512),[[c,t(e).gender]]),t(e).errors.gender?(m(),p("div",B,u(t(e).errors.gender),1)):n("",!0)]),_:1},8,["class"]),o(s,{label:"Ngày sinh",class:f({"text-red-400":t(e).errors.date_of_birth})},{default:r(()=>[o(d,{modelValue:t(e).date_of_birth,"onUpdate:modelValue":l[4]||(l[4]=a=>t(e).date_of_birth=a),type:"date"},null,8,["modelValue"]),t(e).errors.date_of_birth?(m(),p("div",M,u(t(e).errors.date_of_birth),1)):n("",!0)]),_:1},8,["class"]),o(s,{label:"Chức vụ"},{default:r(()=>[o(d,{modelValue:t(e).staff_detail.position,"onUpdate:modelValue":l[5]||(l[5]=a=>t(e).staff_detail.position=a),type:"text",placeholder:"Nhập chức vụ"},null,8,["modelValue"])]),_:1}),o(s,{label:"Phòng ban"},{default:r(()=>[o(d,{modelValue:t(e).staff_detail.department,"onUpdate:modelValue":l[6]||(l[6]=a=>t(e).staff_detail.department=a),type:"text",placeholder:"Nhập phòng ban"},null,8,["modelValue"])]),_:1}),o(s,{label:"Ngày vào làm"},{default:r(()=>[o(d,{modelValue:t(e).staff_detail.hire_date,"onUpdate:modelValue":l[7]||(l[7]=a=>t(e).staff_detail.hire_date=a),type:"date"},null,8,["modelValue"])]),_:1}),o(w,null,{default:r(()=>[o(b,{type:"submit",color:"info",label:"Thêm nhân viên",loading:t(e).processing},null,8,["loading"]),o(b,{type:"button",color:"danger",label:"Hủy",onClick:l[8]||(l[8]=a=>_("update:modelValue",!1))})]),_:1})],32)]),_:1},8,["icon"])):n("",!0)}};export{ue as default};