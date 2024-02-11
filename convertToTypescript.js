const fs = require('fs');
const path = require('path');
const outputFile = 'react-app/types/schemas.ts';
const outputDir = path.dirname(outputFile);

if (!fs.existsSync(outputDir)){
    fs.mkdirSync(outputDir, { recursive: true });
}

fs.readFile('src/Types/Schemas.json', 'utf8', (err, data) => {
    if (err) {
        console.error("Error trying to read file:", err);
        return;
    }

    const schemas = JSON.parse(data);
    let typescriptOutput = '';

    for (const className in schemas) {
        typescriptOutput += `export type ${className} = {\n`;
        for (const property in schemas[className]) {
            typescriptOutput += `    ${property}: ${schemas[className][property]},\n`;
        }
        typescriptOutput += '};\n\n';
    }

    fs.writeFile('react-app/types/schemas.ts', typescriptOutput, (err) => {
        if (err) {
            return console.error("Error when trying to write types: ", err);
        }
            return console.log("TypeScript generated successfully.");
        
    });
});