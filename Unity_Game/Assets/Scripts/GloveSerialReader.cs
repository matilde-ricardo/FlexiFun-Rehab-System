using System;
using System.IO.Ports;
using System.Threading;
using UnityEngine;

public class GloveSerialReader : MonoBehaviour
{
    public string portName = "COM6";
    public int baudRate = 115200;

    public int fsrRaw;
    public float yawDeg;

    SerialPort _serial;
    Thread _thread;
    volatile bool _running;

    volatile int _latestFsr;
    volatile float _latestYaw;

    void Start()
    {
        try
        {
            _serial = new SerialPort(portName, baudRate);
            _serial.NewLine = "\n";
            _serial.ReadTimeout = 50;
            _serial.Open();

            _running = true;
            _thread = new Thread(ReadLoop) { IsBackground = true };
            _thread.Start();
        }
        catch (Exception e)
        {
            Debug.LogError($"[Glove] Não consegui abrir {portName}: {e.Message}");
        }
    }

    void ReadLoop()
    {
        while (_running && _serial != null && _serial.IsOpen)
        {
            try
            {
                var line = _serial.ReadLine().Trim(); // "FSR=123,YAW=4.5"
                int fsr = _latestFsr;
                float yaw = _latestYaw;

                var parts = line.Split(',');
                foreach (var p in parts)
                {
                    if (p.StartsWith("FSR=") && int.TryParse(p.Substring(4), out int vFsr))
                        fsr = vFsr;

                    if (p.StartsWith("YAW=") && float.TryParse(p.Substring(4),
                        System.Globalization.NumberStyles.Float,
                        System.Globalization.CultureInfo.InvariantCulture, out float vYaw))
                        yaw = vYaw;
                }

                _latestFsr = fsr;
                _latestYaw = yaw;
            }
            catch { }
        }
    }

    void Update()
    {
        fsrRaw = _latestFsr;
        yawDeg = _latestYaw;
    }

    void OnDisable()
    {
        _running = false;
        try { _thread?.Join(200); } catch { }
        try { if (_serial != null && _serial.IsOpen) _serial.Close(); } catch { }
    }

    void OnApplicationQuit()
    {
        OnDisable();
    }
}