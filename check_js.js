const fs = require('fs');
const content = fs.readFileSync('dump.html', 'utf8');

// A more robust regex for script tags
const scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let count = 0;

while ((match = scriptRegex.exec(content)) !== null) {
    let scriptCode = match[1];
    if (scriptCode && scriptCode.trim().length > 0) {
        try {
            // We can't always evaluate jQuery stuff easily, but we can check syntax using vm
            new (require('vm').Script)(scriptCode);
        } catch (e) {
            console.log('--- SYNTAX ERROR IN SCRIPT ' + count + ' ---');
            console.log(e.message);
            // Print the lines around the error
            let lines = scriptCode.split('\n');
            let errLineMatch = e.stack.match(/evalmachine\.<anonymous>:(\d+)/);
            if (errLineMatch) {
                let lineNum = parseInt(errLineMatch[1]);
                console.log('Error around line ' + lineNum + ':');
                for (let i = Math.max(0, lineNum - 5); i < Math.min(lines.length, lineNum + 5); i++) {
                    console.log((i+1) + ': ' + lines[i]);
                }
            } else {
                console.log(e.stack);
            }
        }
    }
    count++;
}
console.log("Checked " + count + " scripts.");
