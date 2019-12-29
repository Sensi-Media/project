
const fs = require('fs');
const md5 = require('md5');

const env = process.env.GRUNT_ENV || 'dev';

module.exports = {
    versions: {
        src: 'src/template.html.twig',
        dest: 'Versions.json',
        options: {
            process: function (content, srcpath) {
                let files = ['required.css', 'optional.css', 'bundle.js'];
                let versions = {};
                files.map(file => {
                    let dir = file.match(/\.(css|js)$/)[1];
                    versions[dir + '/' + file] = md5(fs.readFileSync('httpdocs/' + dir + '/' + file, {encoding: 'utf8'})).substring(0, 8);
                });
                return JSON.stringify(versions);
            }
        }
    }
};

