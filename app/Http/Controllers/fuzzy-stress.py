import numpy as np
import sys
import skfuzzy as fuzz
from skfuzzy import control as ctrl

def fuzzy(heart_input, oxy_input):
    # New Antecedent/Consequent objects hold universe variables and membership
    heart_rate = ctrl.Antecedent(np.arange(0, 150, 1), 'heart_rate')
    heart_rate['1'] = fuzz.trimf(heart_rate.universe, [0, 35, 70])
    heart_rate['2'] = fuzz.trimf(heart_rate.universe, [69, 75, 90])
    heart_rate['3'] = fuzz.trimf(heart_rate.universe, [89, 95, 100])
    heart_rate['4'] = fuzz.trimf(heart_rate.universe, [99, 125, 150])

    oxy_rate = ctrl.Antecedent(np.arange(0, 100, 1), 'oxy_rate')
    oxy_rate['below'] = fuzz.trimf(oxy_rate.universe, [0, 47, 94])
    oxy_rate['normal'] = fuzz.trimf(oxy_rate.universe, [93, 96, 100])

    stress_rate = ctrl.Consequent(np.arange(0, 100, 1), 'stress_rate')
    stress_rate['relax'] = fuzz.trimf(stress_rate.universe, [0, 12, 25])
    stress_rate['calm'] = fuzz.trimf(stress_rate.universe, [20, 35, 50])
    stress_rate['anxious'] = fuzz.trimf(stress_rate.universe, [45, 60, 75])
    stress_rate['stress'] = fuzz.trimf(stress_rate.universe, [70, 85, 100])

    # heart_rate.view()
    # stress_rate.view()

    # Custom rule
    rule1 = ctrl.Rule(heart_rate['1'] & oxy_rate['normal'], stress_rate['relax'])
    rule2 = ctrl.Rule(heart_rate['1'] & oxy_rate['below'], stress_rate['calm'])
    rule3 = ctrl.Rule(heart_rate['2'] & oxy_rate['normal'], stress_rate['calm'])
    rule4 = ctrl.Rule(heart_rate['2'] & oxy_rate['below'], stress_rate['anxious'])
    rule5 = ctrl.Rule(heart_rate['3'] & oxy_rate['normal'], stress_rate['anxious'])
    rule6 = ctrl.Rule(heart_rate['3'] & oxy_rate['below'], stress_rate['stress'])
    rule7 = ctrl.Rule(heart_rate['4'] & oxy_rate['normal'], stress_rate['stress'])
    rule7 = ctrl.Rule(heart_rate['4'] & oxy_rate['below'], stress_rate['stress'])

    # Aggregated rule
    stress_ctrl = ctrl.ControlSystem([rule1, rule2, rule3, rule4, rule5, rule6, rule7])
    stress_level = ctrl.ControlSystemSimulation(stress_ctrl)

    # Pass inputs to the control system
    
    # while input("Press Enter to continue or 'q' to quit: ") != 'q':
    #     print("Enter heart-rate: ")
    #     stress_input = int(input(">"))
    #     stress_level.input['heart_rate'] = stress_input
    #     stress_level.compute()
    #     stress_number = stress_level.output['stress_rate']
    #     if stress_number <= 25:
    #         print("Relax")
    #     elif stress_number <= 50:
    #         print("Calm")
    #     elif stress_number <= 75:
    #         print("Anxious")
    #     else:
    #         print("Stressed")
        # return(stress_level.output['stress_rate'])

    # print("Enter heart-rate: ")
    # heart_input = int(input(">")) #gimana caranya bisa masukkin nilai heart rate ke fungsi ini?
    # print("Enter oxy-rate: ")
    # oxy_input = int(input(">")) #gimana caranya bisa masukkin nilai oxy rate ke fungsi ini?

    stress_level.input['heart_rate'] = heart_input
    stress_level.input['oxy_rate'] = oxy_input
    stress_level.compute()
        
    # print("Enter heart-rate: ")
    # stress_input = int(input(">"))
    # stress_level.input['heart_rate'] = stress_input
    # stress_level.compute()

    return(stress_level.output['stress_rate'])

if __name__ == '__main__':
    stress_number = fuzzy(int(sys.argv[1]), int(sys.argv[2]))

    # Print fuzzy output
    print(stress_number, end=" ")
    if stress_number <= 25:
        print("Relax")
    elif stress_number <= 50:
        print("Calm")
    elif stress_number <= 75:
        print("Anxious")
    else:
        print("Stressed")
