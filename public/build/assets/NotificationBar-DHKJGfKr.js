import{i as p}from"./@mdi-ybpv6R7X.js";import{b as h,c as _}from"./colors-S7_1wET5.js";import{_ as y}from"./BaseLevel-vqqndN8O.js";import{_ as b}from"./BaseIcon-B4kwkOCn.js";import{_ as g}from"./BaseButton-eMTGofBM.js";import{c as l,r as k,bt as x,aU as s,a2 as B,aa as v,bH as w,a3 as r,a0 as n,a1 as i,b1 as c,bl as C,j as S}from"./@vue-BRgAy5zV.js";const $={class:"flex flex-col md:flex-row items-center"},N={class:"text-center md:text-left md:py-2"},L={__name:"NotificationBar",props:{icon:{type:String,default:null},outline:Boolean,color:{type:String,required:!0}},setup(e){const o=e,m=l(()=>o.outline?h[o.color]:_[o.color]),t=k(!1),d=()=>{t.value=!0},u=x(),f=l(()=>u.right);return(a,V)=>t.value?i("",!0):(s(),B("div",{key:0,class:S([m.value,"px-3 py-6 md:py-3 mb-6 last:mb-0 border rounded-lg transition-colors duration-150"])},[v(y,null,{default:w(()=>[r("div",$,[e.icon?(s(),n(b,{key:0,path:e.icon,w:"w-10 md:w-5",h:"h-10 md:h-5",size:"24",class:"md:mr-2"},null,8,["path"])):i("",!0),r("span",N,[c(a.$slots,"default")])]),f.value?c(a.$slots,"right",{key:0}):(s(),n(g,{key:1,icon:C(p),small:"","rounded-full":"",color:"white",onClick:d},null,8,["icon"]))]),_:3})],2))}};export{L as _};