"use strict";var l=Object.create;var e=Object.defineProperty;var m=Object.getOwnPropertyDescriptor;var d=Object.getOwnPropertyNames;var f=Object.getPrototypeOf,g=Object.prototype.hasOwnProperty;var O=(t,o)=>{for(var i in o)e(t,i,{get:o[i],enumerable:!0})},a=(t,o,i,s)=>{if(o&&typeof o=="object"||typeof o=="function")for(let r of d(o))!g.call(t,r)&&r!==i&&e(t,r,{get:()=>o[r],enumerable:!(s=m(o,r))||s.enumerable});return t};var h=(t,o,i)=>(i=t!=null?l(f(t)):{},a(o||!t||!t.__esModule?e(i,"default",{value:t,enumerable:!0}):i,t)),j=t=>a(e({},"__esModule",{value:!0}),t);var y={};O(y,{steward:()=>u});module.exports=j(y);var n=h(require("fs"),1),c="./public/vendor/js",v={outputDir:c};function w(t={}){return{name:"vite-plugin-markdoc-content",async buildStart(){let o=Object.assign({},v,t),i=[{name:"color-mode.js",path:"resources/js/color-mode.js"},{name:"tiptap.js",path:"dist/tiptap.cjs"}],s=`${process.cwd()}/vendor/kiwilan/laravel-steward`;await n.default.promises.mkdir(o.outputDir??c,{recursive:!0}).catch(console.error);for(let r of i)n.default.copyFile(`${s}/${r.path}`,`${o.outputDir}/${r.name}`,p=>{if(p)throw p})}}}var u=w;0&&(module.exports={steward});
