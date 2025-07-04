require("dotenv").config();
const express = require("express");
const axios = require("axios");
const path = require("path");

const app = express();
app.use(express.json());

const sandbox = "https://sandbox.vtpass.com/api";

// Generate request_id with Africa/Lagos time prefix
function generateRequestId(suffix = "") {
  const now = new Date();
  const fmt = new Intl.DateTimeFormat("en-GB", {
    timeZone: "Africa/Lagos",
    year: "numeric", month: "2-digit", day: "2-digit",
    hour: "2-digit", minute: "2-digit", hour12: false,
  });
  const parts = fmt.formatToParts(now);
  const map = Object.fromEntries(parts.map(p => [p.type, p.value]));
  const prefix = map.year + map.month + map.day + map.hour + map.minute;
  const randomSuffix = suffix || Math.random().toString(36).slice(2, 10);
  return prefix + randomSuffix;
}

app.get("/", (_req, res) => res.send("VTpass Sandbox Server is running!"));
app.get("/test", (_req, res) => res.sendFile(path.join(__dirname, "index.html")));

app.get("/plans/data/:network", async (req, res) => {
  const serviceID = `${req.params.network}-data`;
  try {
    const resp = await axios.get(`${sandbox}/service-variations?serviceID=${serviceID}`, {
      headers: { "api-key": process.env.VT_API_KEY, "public-key": process.env.VT_PUBLIC_KEY }
    });
    res.json(resp.data);
  } catch (err) {
    res.status(500).json({ error: err.response?.data || err.message });
  }
});

app.post("/buy", async (req, res) => {
  const { phone, serviceID, amount, variation_code } = req.body;
  const request_id = generateRequestId();
  const payload = { request_id, serviceID, phone };

  if (variation_code) {
    payload.variation_code = variation_code;
  } else if (amount) {
    payload.amount = amount;
  } else {
    return res.status(400).json({ error: "Missing amount or variation_code" });
  }

  try {
    const resp = await axios.post(`${sandbox}/pay`, payload, {
      headers: {
        "api-key": process.env.VT_API_KEY,
        "secret-key": process.env.VT_SECRET_KEY,
        "Content-Type": "application/json",
      },
    });
    res.json(resp.data);
  } catch (err) {
    res.status(500).json({ error: err.response?.data || err.message });
  }
});

app.post("/requery", async (req, res) => {
  try {
    const resp = await axios.post(`${sandbox}/requery`, {
      request_id: req.body.request_id
    }, {
      headers: {
        "api-key": process.env.VT_API_KEY,
        "secret-key": process.env.VT_SECRET_KEY,
        "Content-Type": "application/json",
      },
    });
    res.json(resp.data);
  } catch (err) {
    res.status(500).json({ error: err.response?.data || err.message });
  }
});

app.listen(3000, () => console.log("Server running at http://localhost:3000"));
