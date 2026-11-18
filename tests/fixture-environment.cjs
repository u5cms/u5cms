'use strict';
const fixturePassword=process.env.U5CMS_FIXTURE_PASSWORD;
const fixtureBody=process.env.U5CMS_FIXTURE_BODY;
if(!fixturePassword || fixturePassword.length<32 || !fixtureBody || fixtureBody.length<32)
 throw new Error('Run the Python fixture driver; ephemeral test values are required.');
const fixturePasswordFor=scope=>require('node:crypto').createHmac('sha256',fixturePassword).update(scope).digest('hex');
module.exports={fixturePassword,fixtureBody,fixturePasswordFor};
