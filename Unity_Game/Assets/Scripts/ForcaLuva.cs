using UnityEngine;
using UnityEngine.UI;
using System.IO.Ports; // Necessário para a comunicação Serial

public class ForcaLuva : MonoBehaviour
{
    // Mude "COM3" para a porta onde o seu Arduino está ligado
    SerialPort porta = new SerialPort("COM3", 9600); 
    
    [Header("Referências UI")]
    public Slider sliderForca; // Arraste o Slider aqui no Inspector
    
    [Header("Configurações")]
    [Tooltip("O valor máximo que o analogRead do Arduino envia (normalmente 1023).")]
    public float valorMaximoSensor = 1023f; 

    void Start()
    {
        try 
        {
            porta.Open();
            // Define um tempo de espera curto para não "congelar" o Unity se não houver dados
            porta.ReadTimeout = 10; 
            Debug.Log("Conexão com a luva estabelecida!");
        }
        catch (System.Exception e)
        {
            Debug.LogError("Erro ao conectar à luva: " + e.Message);
        }
    }

    void Update()
    {
        if (porta.IsOpen)
        {
            try
            {
                // Lê a linha de texto enviada pelo Serial.println() do Arduino
                string valorLido = porta.ReadLine();
                
                // Tenta converter o texto para um número decimal
                if (float.TryParse(valorLido, out float forcaFinal))
                {
                    AtualizarBarra(forcaFinal);
                }
            }
            catch (System.TimeoutException) 
            { 
                // Ignora se o Arduino não enviar nada neste frame para manter o jogo fluido
            }
        }
    }

    void AtualizarBarra(float valor)
    {
        if (sliderForca != null)
        {
            // Calcula a percentagem (valor atual / máximo) para o Slider (que vai de 0 a 1)
            sliderForca.value = valor / valorMaximoSensor;
        }
    }

    // Fecha a porta USB quando o jogo para, para não bloquear o Arduino
    void OnApplicationQuit()
    {
        if (porta.IsOpen) 
        {
            porta.Close();
        }
    }
}