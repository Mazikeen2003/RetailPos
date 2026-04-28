const fs = require('fs');
const parser = require('@babel/parser');
const code = fs.readFileSync('c:/Users/Gershan/RetailPos/frontend/src/App.js','utf8');
try{
  parser.parse(code, {sourceType:'module', plugins:['jsx','flow','classProperties','optionalChaining']});
  console.log('Parsed OK');
}catch(e){
  console.error('Error:', e.message);
  console.error(e.loc);
}
