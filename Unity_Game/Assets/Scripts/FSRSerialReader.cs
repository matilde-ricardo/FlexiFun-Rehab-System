using System;
using System.IO.Ports;
using System.Threading;
using UnityEngine;

public class FSRSerialReader : MonoBehaviour
{
    [Header("Serial")]
    public string portName = "COM6";
    public int baudRate = 115200;

    [Header("FSR (ESP32)")]
    public int maxRaw = 4095;     // ESP32
    public int threshold = 200;   // fixo (sem calibração)

    [Header("UI (opcional)")]
    public ForceUI forceUI;       // arrasta aqui o componente ForceUI

    [Header("Debug")]
    public int raw;
    public float force01;
    public bool isPressed;

    SerialPort _serial;
    Thread _thread;
    volatile bool _running;
    volatile int _latestRaw;

    void Start()
    {
        OpenPort();
        StartReaderThread();
    }

    void OpenPort()
    {
        try
        {
            _serial = new SerialPort(portName, baudRate);
            _serial.NewLine = "\n";
            _serial.ReadTimeout = 100;
            _serial.Open();
            Debug.Log($"[FSR] Porta aberta: {portName} @ {baudRate}");
        }
        catch (Exception e)
        {
            Debug.LogError($"[FSR] Não consegui abrir {portName}: {e.Message}");
        }
    }

    void StartReaderThread()
    {
        if (_serial == null || !_serial.IsOpen) return;

        _running = true;
        _thread = new Thread(ReadLoop) { IsBackground = true };
        _thread.Start();
    }

    void ReadLoop()
    {
        while (_running && _serial != null && _serial.IsOpen)
        {
            try
            {
                var line = _serial.ReadLine();
                if (int.TryParse(line.Trim(), out int v))
                    _latestRaw = v;
            }
            catch { }
        }
    }

    void Update()
    {
        raw = _latestRaw;
        force01 = Mathf.Clamp01((float)raw / maxRaw);
        isPressed = raw >= threshold;

        // Atualiza UI (sem calibração)
        if (forceUI != null)
            forceUI.SetForce(raw); // usa raw diretamente (maxForce no ForceUI deve bater certo)
        // alternativa: forceUI.SetForce(force01 * forceUI.maxForce);
    }

    void OnApplicationQuit()
    {
        _running = false;
        try { _thread?.Join(200); } catch { }
        try { if (_serial != null && _serial.IsOpen) _serial.Close(); } catch { }
    }
}