const express = require('express');
const AWS = require('aws-sdk');

const app = express();
app.use(express.json());

// Load credentials from the shared credentials file
AWS.config.loadFromPath('awscreds.json');

const polly = new AWS.Polly();

app.post('/synthesize', async (req, res) => {
  const params = {
    OutputFormat: 'mp3',
    Text: req.body.text,
    VoiceId: 'Kendra'
  };

  try {
    const data = await polly.synthesizeSpeech(params).promise();
    res.send(data.AudioStream);
  } catch (error) {
    res.status(500).send(error);
  }
});

const port = process.env.PORT || 3000;
app.listen(port, () => console.log(`Server running on port ${port}`));
