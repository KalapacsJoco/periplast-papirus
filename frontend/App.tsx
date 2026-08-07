import './i18n';
import { StatusBar } from 'expo-status-bar';
import Constants from 'expo-constants';
import { useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Modal,
  Platform,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';

type Machine = {
  id: number;
  name: string;
  type: string;
  open_mm: number | null;
  screw_diameter_cm: number | null;
  status: string;
  problem_description?: string;
};

type Order = {
  id: number;
  order_number: string;
  machine_id: number;
  mould_id: number;
  product_id: number;
  date?: string;
  customer?: string;
  quantity: number;
  current_quantity?: number;
  good_quantity?: number;
  bad_quantity?: number;
  started_at?: string | null;
  ended_at?: string | null;
  last_paused_at?: string | null;
  cycle_time: number | null;
  net_weight: string | number | null;
  gross_weight: string | number | null;
  color_percentage: string | number | null;
  additives_percentages?: Array<{ additive_id: number; percentage: number }> | null;
  production_protocol?: {
    machine_name?: string | null;
    part_code?: string | null;
    material_code?: string | null;
    color_code?: string | null;
    color_percentage?: number | null;
    additives?: string | null;
    gross_weight?: number | null;
    net_weight?: number | null;
  } | null;
  status: string;
  machine?: { id: number; name: string; status?: string; problem_description?: string };
  mould?: { id: number; name: string; number: string; cavity_count?: number | null; status?: string; problem_description?: string };
  product?: { id: number; name: string; code: string; is_active?: boolean };
};

type Mould = {
  id: number;
  name: string;
  type: string;
  number: string;
  cavity_count: number | null;
  open_mm: number | null;
  status: string;
  problem_description?: string;
  product?: { id: number; name: string };
};

type Product = {
  id: number;
  name: string;
  code: string;
  is_active?: boolean;
  material?: { id: number; name: string; code: string };
  color?: { id: number; name: string; code: string };
  additives?: Array<{ id: number; name: string; code: string }>;
  moulds?: Array<{ id: number; name: string; number: string }>;
};

type DashboardMachine = Machine & {
  currentOrder: Order | null;
};

type Screen = 'machines' | 'moulds' | 'products';
type IssueTarget = 'machine' | 'mould' | 'product';

type ProductionProtocolDraft = {
  machine_name: string;
  part_code: string;
  material_code: string;
  color_code: string;
  color_percentage: string;
  additives: string;
  cycle_time: string;
  quantity: string;
  gross_weight: string;
  net_weight: string;
};

export default function App() {
  const { t, i18n } = useTranslation();
  const [currentScreen, setCurrentScreen] = useState<Screen>('machines');
  const [machines, setMachines] = useState<Machine[]>([]);
  const [orders, setOrders] = useState<Order[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [isOrdersLoading, setIsOrdersLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [ordersError, setOrdersError] = useState<string | null>(null);
  const [hasLoaded, setHasLoaded] = useState(false);
  const [ordersLoaded, setOrdersLoaded] = useState(false);
  const [productionDrafts, setProductionDrafts] = useState<Record<number, { current_quantity: string }>>({});
  const [selectedMachine, setSelectedMachine] = useState<Machine | null>(null);
  const [showModal, setShowModal] = useState(false);
  const [showMenu, setShowMenu] = useState(false);
  const [issueTarget, setIssueTarget] = useState<IssueTarget>('machine');
  const [newStatus, setNewStatus] = useState<string>('');
  const [problemDescription, setProblemDescription] = useState<string>('');
  const [isUpdating, setIsUpdating] = useState(false);
  const [showProtocolModal, setShowProtocolModal] = useState(false);
  const [protocolOrderId, setProtocolOrderId] = useState<number | null>(null);
  const [protocolDraft, setProtocolDraft] = useState<ProductionProtocolDraft | null>(null);
  const [isProtocolSaving, setIsProtocolSaving] = useState(false);
  const [protocolPromptedOrderIds, setProtocolPromptedOrderIds] = useState<number[]>([]);
  const [showMachineOrdersModal, setShowMachineOrdersModal] = useState(false);
  const [machineOrdersMachine, setMachineOrdersMachine] = useState<DashboardMachine | null>(null);
  const [activatingOrderId, setActivatingOrderId] = useState<number | null>(null);
  const [showFinalizeModal, setShowFinalizeModal] = useState(false);
  const [finalizeOrderId, setFinalizeOrderId] = useState<number | null>(null);
  const [finalizeStatus, setFinalizeStatus] = useState<'completed' | 'interrupted'>('completed');
  const [finalizeGoodQuantity, setFinalizeGoodQuantity] = useState('0');
  const [finalizeBadQuantity, setFinalizeBadQuantity] = useState('0');

  // Moulds state
  const [moulds, setMoulds] = useState<Mould[]>([]);
  const [isMouldsLoading, setIsMouldsLoading] = useState(false);
  const [mouldsError, setMouldsError] = useState<string | null>(null);
  const [mouldsLoaded, setMouldsLoaded] = useState(false);
  const [selectedMould, setSelectedMould] = useState<Mould | null>(null);
  const [showMouldModal, setShowMouldModal] = useState(false);
  const [newMouldStatus, setNewMouldStatus] = useState<string>('');
  const [mouldProblemDescription, setMouldProblemDescription] = useState<string>('');
  const [isMouldUpdating, setIsMouldUpdating] = useState(false);

  // Products state
  const [products, setProducts] = useState<Product[]>([]);
  const [isProductsLoading, setIsProductsLoading] = useState(false);
  const [productsError, setProductsError] = useState<string | null>(null);
  const [productsLoaded, setProductsLoaded] = useState(false);

  const apiBaseUrl = useMemo(() => {
    const overrideUrl = process.env.EXPO_PUBLIC_API_URL;
    if (overrideUrl) return overrideUrl;

    const hostUri = Constants.expoConfig?.hostUri ?? Constants.expoGoConfig?.debuggerHost ?? null;
    if (hostUri) {
      const host = hostUri.split(':')[0];
      return `http://${host}:8000`;
    }

    if (Platform.OS === 'android') return 'http://10.0.2.2:8000';
    return 'http://127.0.0.1:8000';
  }, []);

  useEffect(() => {
    loadMachines();
    loadOrders();
  }, []);


  const loadMachines = async () => {
    setIsLoading(true);
    setErrorMessage(null);

    try {
      const response = await fetch(`${apiBaseUrl}/api/machines`);
      if (!response.ok) throw new Error(`${t('api_error')}: ${response.status}`);

      const data = (await response.json()) as Machine[];
      setMachines(data);
      setHasLoaded(true);
    } catch (error) {
      setErrorMessage(
        error instanceof Error ? error.message : t('unknown_error')
      );
    } finally {
      setIsLoading(false);
    }
  };

  const loadOrders = async () => {
    setIsOrdersLoading(true);
    setOrdersError(null);

    try {
      const response = await fetch(`${apiBaseUrl}/api/orders`);
      if (!response.ok) throw new Error(`${t('api_error')}: ${response.status}`);

      const data = (await response.json()) as Order[];
      setOrders(data);
      setProductionDrafts((current) => {
        const next = { ...current };

        data.forEach((order) => {
          if (!next[order.id]) {
            next[order.id] = {
              current_quantity: String(order.current_quantity ?? 0),
            };
          }
        });

        return next;
      });
      setOrdersLoaded(true);
    } catch (error) {
      setOrdersError(
        error instanceof Error ? error.message : t('unknown_error')
      );
    } finally {
      setIsOrdersLoading(false);
    }
  };

  const openStatusModal = (machine: Machine) => {
    setSelectedMachine(machine);
    setNewStatus(machine.status);
    setProblemDescription(machine.problem_description || '');
    setShowModal(true);
  };

  const openIssueModal = (machine: DashboardMachine, target: IssueTarget = 'machine') => {
    setSelectedMachine(machine);
    setIssueTarget(target);

    if (target === 'machine') {
      setNewStatus(machine.status);
      setProblemDescription(machine.problem_description || '');
    } else if (target === 'mould') {
      const mould = machine.currentOrder?.mould;
      setNewStatus(mould?.status || 'active');
      setProblemDescription(mould?.problem_description || '');
    } else {
      setNewStatus(machine.currentOrder?.product?.is_active ? 'active' : 'inactive');
      setProblemDescription('');
    }

    setShowModal(true);
  };

  const updateMachineStatus = async () => {
    if (!selectedMachine) return;
    setIsUpdating(true);

    try {
      let response: Response;

      if (issueTarget === 'machine') {
        response = await fetch(`${apiBaseUrl}/api/machines/${selectedMachine.id}/status`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ status: newStatus, problem_description: problemDescription }),
        });

        if (!response.ok) throw new Error(`${t('error')}: ${response.status}`);
        const updated = (await response.json()) as Machine;
        setMachines(machines.map(m => (m.id === updated.id ? updated : m)));
        await loadOrders();
      } else if (issueTarget === 'mould') {
        const mouldId = selectedMachine.currentOrder?.mould_id;
        if (!mouldId) throw new Error(t('unknown_error'));

        response = await fetch(`${apiBaseUrl}/api/moulds/${mouldId}/status`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ status: newStatus, problem_description: problemDescription }),
        });

        if (!response.ok) throw new Error(`${t('error')}: ${response.status}`);
        const updated = (await response.json()) as Mould;
        setOrders(orders.map(order => order.mould_id === updated.id ? { ...order, mould: { ...updated } } : order));
        setMoulds(moulds.map(m => (m.id === updated.id ? updated : m)));
        await loadOrders();
      } else {
        const productId = selectedMachine.currentOrder?.product_id;
        if (!productId) throw new Error(t('unknown_error'));

        response = await fetch(`${apiBaseUrl}/api/products/${productId}/status`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ status: newStatus }),
        });

        if (!response.ok) throw new Error(`${t('error')}: ${response.status}`);
        const updated = (await response.json()) as Product;
        setOrders(orders.map(order => order.product_id === updated.id ? { ...order, product: { ...updated } } : order));
        setProducts(products.map(p => (p.id === updated.id ? updated : p)));
      }

      setShowModal(false);
      Alert.alert(t('success'), t('status_updated'));
    } catch (error) {
      Alert.alert(t('error'), error instanceof Error ? error.message : t('unknown_error'));
    } finally {
      setIsUpdating(false);
    }
  };

  const restoreMachine = async (machine: Machine) => {
    Alert.alert(t('restore_title'), t('restore_message'), [
      { text: t('cancel'), onPress: () => {}, style: 'cancel' },
      {
        text: t('restore_confirm'),
        onPress: async () => {
          setIsUpdating(true);
          try {
            const response = await fetch(`${apiBaseUrl}/api/machines/${machine.id}/restore`, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
            });

            if (!response.ok) throw new Error(`${t('error')}: ${response.status}`);
            const restored = (await response.json()) as Machine;
            setMachines(machines.map(m => m.id === restored.id ? restored : m));
            Alert.alert(t('success'), t('restore_success'));
          } catch (error) {
            Alert.alert(t('error'), error instanceof Error ? error.message : t('unknown_error'));
          } finally {
            setIsUpdating(false);
          }
        },
      },
    ]);
  };

  const loadMoulds = async () => {
    setIsMouldsLoading(true);
    setMouldsError(null);
    try {
      const response = await fetch(`${apiBaseUrl}/api/moulds`);
      if (!response.ok) throw new Error(`${t('api_error')}: ${response.status}`);
      const data = (await response.json()) as Mould[];
      setMoulds(data);
      setMouldsLoaded(true);
    } catch (error) {
      setMouldsError(error instanceof Error ? error.message : t('unknown_error'));
    } finally {
      setIsMouldsLoading(false);
    }
  };

  const dashboardMachines = useMemo<DashboardMachine[]>(() => {
    return machines.map((machine) => {
      const currentOrder = orders.find(
        (order) => order.machine_id === machine.id && order.status === 'in_progress'
      ) ?? null;

      return { ...machine, currentOrder };
    });
  }, [machines, orders]);

  const activeOrdersCount = useMemo(
    () => orders.filter((order) => order.status === 'in_progress').length,
    [orders]
  );

  const getDashboardStatusColor = (active: boolean) => (active ? '#22c55e' : '#999999');

  const getDashboardStatusLabel = (active: boolean) =>
    active ? t('machine_status_active') : t('machine_status_inactive');

  const getIssueStatusColor = (target: IssueTarget, status: string) => {
    if (target === 'product') return status === 'active' ? '#22c55e' : '#999999';

    switch (status) {
      case 'active':
        return '#22c55e';
      case 'standby':
      case 'stopped':
        return '#999999';
      case 'warning':
        return '#f59e0b';
      case 'broken':
      case 'needs_repair':
        return '#ef4444';
      case 'under_repair':
        return '#3b82f6';
      default:
        return '#999999';
    }
  };

  const getIssueStatusLabel = (target: IssueTarget, status: string) => {
    if (target === 'product') {
      return status === 'active' ? t('status_active') : t('status_inactive');
    }

    switch (status) {
      case 'active':
        return t('status_active');
      case 'stopped':
        return t('status_stopped');
      case 'standby':
        return t('mould_status_standby');
      case 'warning':
        return t('status_warning');
      case 'broken':
        return t('status_broken');
      case 'needs_repair':
        return t('mould_status_needs_repair');
      case 'under_repair':
        return t('mould_status_under_repair');
      default:
        return status;
    }
  };

  const getIssueStatuses = (target: IssueTarget) => {
    if (target === 'machine') return ['active', 'stopped', 'warning', 'broken'];
    if (target === 'mould') return ['active', 'standby', 'warning', 'needs_repair', 'under_repair'];
    return ['active', 'inactive'];
  };

  const requiresProblemDescription = (target: IssueTarget, status: string) => {
    if (target === 'machine') return ['warning', 'broken'].includes(status);
    if (target === 'mould') return ['warning', 'needs_repair'].includes(status);
    return false;
  };

  const getProductionDraft = (order: Order) => {
    return productionDrafts[order.id] ?? {
      current_quantity: String(order.current_quantity ?? 0),
    };
  };

  const updateProductionDraft = (orderId: number, field: 'current_quantity', value: string) => {
    setProductionDrafts((current) => ({
      ...current,
      [orderId]: {
        ...current[orderId],
        [field]: value,
      },
    }));
  };

  const hasProtocolData = (order: Order) => {
    const protocol = order.production_protocol;
    if (!protocol) return false;

    return Boolean(
      protocol.part_code ||
      protocol.material_code ||
      protocol.color_code ||
      protocol.additives ||
      protocol.gross_weight != null ||
      protocol.net_weight != null
    );
  };

  const buildProtocolDraft = (order: Order): ProductionProtocolDraft => {
    const protocol = order.production_protocol ?? null;

    return {
      machine_name: protocol?.machine_name ?? order.machine?.name ?? '',
      part_code: protocol?.part_code ?? order.product?.code ?? '',
      material_code: protocol?.material_code ?? '',
      color_code: protocol?.color_code ?? '',
      color_percentage: protocol?.color_percentage != null ? String(protocol.color_percentage) : (order.color_percentage != null ? String(order.color_percentage) : ''),
      additives: protocol?.additives ?? '',
      cycle_time: protocol?.cycle_time != null ? String(protocol.cycle_time) : String(order.cycle_time ?? ''),
      quantity: protocol?.quantity != null ? String(protocol.quantity) : String(order.quantity ?? ''),
      gross_weight: protocol?.gross_weight != null ? String(protocol.gross_weight) : (order.gross_weight != null ? String(order.gross_weight) : ''),
      net_weight: protocol?.net_weight != null ? String(protocol.net_weight) : (order.net_weight != null ? String(order.net_weight) : ''),
    };
  };

  const openProtocolModal = (order: Order) => {
    setProtocolOrderId(order.id);
    setProtocolDraft(buildProtocolDraft(order));
    setShowProtocolModal(true);
  };

  useEffect(() => {
    if (showProtocolModal) return;

    const candidate = orders.find(
      (order) =>
        order.status === 'in_progress'
        && !hasProtocolData(order)
        && !protocolPromptedOrderIds.includes(order.id)
    );

    if (candidate) {
      setProtocolPromptedOrderIds((current) => [...current, candidate.id]);
      openProtocolModal(candidate);
    }
  }, [orders, showProtocolModal, protocolPromptedOrderIds]);

  const saveProductionProtocol = async () => {
    if (!protocolOrderId || !protocolDraft) return;

    const colorPercentage = protocolDraft.color_percentage.trim() === '' ? null : Number(protocolDraft.color_percentage);
    const cycleTime = protocolDraft.cycle_time.trim() === '' ? null : Number(protocolDraft.cycle_time);
    const quantity = protocolDraft.quantity.trim() === '' ? null : Number(protocolDraft.quantity);
    const grossWeight = protocolDraft.gross_weight.trim() === '' ? null : Number(protocolDraft.gross_weight);
    const netWeight = protocolDraft.net_weight.trim() === '' ? null : Number(protocolDraft.net_weight);

    if (
      (colorPercentage != null && Number.isNaN(colorPercentage)) ||
      (cycleTime != null && Number.isNaN(cycleTime)) ||
      (quantity != null && Number.isNaN(quantity)) ||
      (grossWeight != null && Number.isNaN(grossWeight)) ||
      (netWeight != null && Number.isNaN(netWeight))
    ) {
      Alert.alert(t('error'), t('unknown_error'));
      return;
    }

    setIsProtocolSaving(true);
    try {
      const response = await fetch(`${apiBaseUrl}/api/orders/${protocolOrderId}/protocol`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          machine_name: protocolDraft.machine_name || null,
          part_code: protocolDraft.part_code || null,
          material_code: protocolDraft.material_code || null,
          color_code: protocolDraft.color_code || null,
          color_percentage: colorPercentage,
          additives: protocolDraft.additives || null,
          cycle_time: cycleTime,
          quantity,
          gross_weight: grossWeight,
          net_weight: netWeight,
        }),
      });

      if (!response.ok) throw new Error(`${t('error')}: ${response.status}`);

      const updated = (await response.json()) as Order;
      setOrders((current) => current.map((item) => (item.id === updated.id ? updated : item)));
      setShowProtocolModal(false);
      Alert.alert(t('success'), t('production_protocol_saved'));
    } catch (error) {
      Alert.alert(t('error'), error instanceof Error ? error.message : t('unknown_error'));
    } finally {
      setIsProtocolSaving(false);
    }
  };

  const isWorkingTime = (date: Date) => {
    const day = date.getDay();
    const hour = date.getHours() + date.getMinutes() / 60;

    if (day === 0) return false;
    if (day === 6) return hour < 5;
    if (day === 5) return true;
    return hour >= 7;
  };

  const nextWorkingStart = (date: Date) => {
    const next = new Date(date);

    if (date.getDay() === 6) {
      next.setDate(date.getDate() + 2);
      next.setHours(7, 0, 0, 0);
      return next;
    }

    if (date.getDay() === 0) {
      next.setDate(date.getDate() + 1);
      next.setHours(7, 0, 0, 0);
      return next;
    }

    if (date.getHours() < 7) {
      next.setHours(7, 0, 0, 0);
      return next;
    }

    return next;
  };

  const workingIntervalEnd = (date: Date) => {
    const end = new Date(date);

    if (date.getDay() === 6 && date.getHours() < 5) {
      end.setHours(5, 0, 0, 0);
      return end;
    }

    const daysUntilSaturday = (6 - date.getDay() + 7) % 7 || 7;
    end.setDate(date.getDate() + daysUntilSaturday);
    end.setHours(5, 0, 0, 0);
    return end;
  };

  const addWorkingSeconds = (start: Date, seconds: number) => {
    let current = new Date(start);
    let remaining = Math.max(0, seconds);

    while (remaining > 0) {
      if (!isWorkingTime(current)) {
        current = nextWorkingStart(current);
        continue;
      }

      const intervalEnd = workingIntervalEnd(current);
      const available = Math.max(0, (intervalEnd.getTime() - current.getTime()) / 1000);
      const consumed = Math.min(available, remaining);
      current = new Date(current.getTime() + consumed * 1000);
      remaining -= consumed;

      if (remaining > 0) {
        current = nextWorkingStart(current);
      }
    }

    return current;
  };

  const workingSecondsBetween = (from: Date, to: Date) => {
    if (to <= from) return 0;

    let current = new Date(from);
    let total = 0;

    while (current < to) {
      if (!isWorkingTime(current)) {
        current = nextWorkingStart(current);
        continue;
      }

      const intervalEnd = workingIntervalEnd(current);
      const segmentEnd = intervalEnd < to ? intervalEnd : to;
      total += Math.max(0, (segmentEnd.getTime() - current.getTime()) / 1000);

      if (segmentEnd >= to) break;
      current = nextWorkingStart(segmentEnd);
    }

    return total;
  };

  const formatDuration = (seconds: number) => {
    const safeSeconds = Math.max(0, Math.round(seconds));
    const hours = Math.floor(safeSeconds / 3600);
    const minutes = Math.floor((safeSeconds % 3600) / 60);
    return `${hours}h ${minutes}m`;
  };

  const saveProductionData = async (order: Order) => {
    const draft = getProductionDraft(order);
    const currentQuantity = Number(draft.current_quantity);

    if (currentQuantity < 0) {
      Alert.alert(t('error'), t('unknown_error'));
      return;
    }

    try {
      const response = await fetch(`${apiBaseUrl}/api/orders/${order.id}/production-data`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          current_quantity: currentQuantity,
        }),
      });

      if (!response.ok) throw new Error(`${t('error')}: ${response.status}`);

      const updated = (await response.json()) as Order;
      setOrders((current) => current.map((item) => (item.id === updated.id ? updated : item)));
      setProductionDrafts((current) => ({
        ...current,
        [updated.id]: {
          current_quantity: String(updated.current_quantity ?? 0),
        },
      }));
      Alert.alert(t('success'), t('production_data_saved'));

      if (updated.status === 'in_progress' && !hasProtocolData(updated)) {
        openProtocolModal(updated);
      }
    } catch (error) {
      Alert.alert(t('error'), error instanceof Error ? error.message : t('unknown_error'));
    }
  };

  const updateOrderStatus = async (order: Order, status: 'paused' | 'completed' | 'interrupted', goodQuantity?: number, badQuantity?: number) => {
    try {
      const payload: Record<string, string | number | null> = {
        status,
      };

      if (status === 'paused') {
        payload.pause_description = t('order_paused_from_dashboard');
      }

      if (status === 'completed' || status === 'interrupted') {
        payload.good_quantity = goodQuantity ?? Number(getProductionDraft(order).current_quantity || 0);
        payload.bad_quantity = badQuantity ?? 0;
      }

      const response = await fetch(`${apiBaseUrl}/api/orders/${order.id}/status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      if (!response.ok) throw new Error(`${t('error')}: ${response.status}`);

      const updated = (await response.json()) as Order;
      setOrders((current) => current.map((item) => (item.id === updated.id ? updated : item)));
      setProductionDrafts((current) => ({
        ...current,
        [updated.id]: {
          current_quantity: String(updated.current_quantity ?? 0),
        },
      }));
      Alert.alert(t('success'), t('order_status_updated'));
    } catch (error) {
      Alert.alert(t('error'), error instanceof Error ? error.message : t('unknown_error'));
    }
  };

  const openFinalizeModal = (order: Order, status: 'completed' | 'interrupted') => {
    const draft = getProductionDraft(order);
    const current = Number(draft.current_quantity || 0);
    setFinalizeOrderId(order.id);
    setFinalizeStatus(status);
    setFinalizeGoodQuantity(String(Number.isFinite(current) ? current : 0));
    setFinalizeBadQuantity('0');
    setShowFinalizeModal(true);
  };

  const submitFinalizeStatus = async () => {
    if (!finalizeOrderId) return;

    const order = orders.find((item) => item.id === finalizeOrderId);
    if (!order) return;

    const good = Number(finalizeGoodQuantity);
    const bad = Number(finalizeBadQuantity);

    if (!Number.isFinite(good) || !Number.isFinite(bad) || good < 0 || bad < 0) {
      Alert.alert(t('error'), t('unknown_error'));
      return;
    }

    await updateOrderStatus(order, finalizeStatus, good, bad);
    setShowFinalizeModal(false);
  };

  const getOrderTracking = (
    order: Order,
    draft?: { current_quantity: string }
  ) => {
    const startedAt = order.started_at ? new Date(order.started_at) : null;
    const endedAt = order.ended_at ? new Date(order.ended_at) : null;
    const pausedAt = order.last_paused_at ? new Date(order.last_paused_at) : null;
    const mouldCount = order.mould?.cavity_count ?? 1;
    const draftQuantity = Number(order.quantity ?? 0);
    const draftCycleTime = Number(order.cycle_time ?? 0);
    const draftCurrentQuantity = Number(draft?.current_quantity ?? order.current_quantity ?? 0);
    const totalPieces = Math.max(1, Number.isFinite(draftQuantity) ? draftQuantity : Number(order.quantity ?? 0));
    const totalCycles = Math.max(1, Math.ceil(totalPieces / Math.max(1, mouldCount)));
    const cycleTime = Math.max(1, Number.isFinite(draftCycleTime) ? draftCycleTime : Number(order.cycle_time ?? 0));
    const totalSeconds = totalCycles * cycleTime;
    const currentPiecesRaw = Number.isFinite(draftCurrentQuantity)
      ? draftCurrentQuantity
      : Number(order.current_quantity ?? 0);
    const currentPieces = Math.max(0, Math.min(totalPieces, currentPiecesRaw));
    const remainingPieces = Math.max(0, totalPieces - currentPieces);
    const remainingCycles = Math.max(0, Math.ceil(remainingPieces / Math.max(1, mouldCount)));
    const remainingSeconds = remainingCycles * cycleTime;
    const now = new Date();
    const trackingReference =
      order.status === 'completed'
        ? (endedAt ?? now)
        : (order.status === 'paused' || order.status === 'interrupted')
          ? (pausedAt ?? now)
          : now;
    const elapsedSeconds = startedAt ? workingSecondsBetween(startedAt, trackingReference) : 0;
    const theoreticalProgress = Math.min(1, totalSeconds > 0 ? elapsedSeconds / totalSeconds : 0);
    const actualProgress = Math.min(1, totalPieces > 0 ? currentPieces / totalPieces : 0);
    const theoreticalEta = startedAt ? addWorkingSeconds(startedAt, totalSeconds) : null;
    const actualEta = startedAt ? addWorkingSeconds(trackingReference, remainingSeconds) : null;
    const delaySeconds = theoreticalEta && actualEta ? Math.max(0, (actualEta.getTime() - theoreticalEta.getTime()) / 1000) : 0;

    return {
      startedAt,
      mouldCount,
      totalPieces,
      totalCycles,
      totalSeconds,
      currentPieces,
      remainingPieces,
      remainingCycles,
      remainingSeconds,
      theoreticalProgress,
      actualProgress,
      theoreticalEta,
      actualEta,
      delaySeconds,
    };
  };

  const formatEta = (date: Date | null) => {
    if (!date) return '-';
    return `${date.toLocaleDateString()} ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
  };

  const percentWidth = (value: number) => {
    const bounded = Math.max(0, Math.min(1, value));
    return `${Math.max(4, Math.round(bounded * 100))}%`;
  };

  const loadProducts = async () => {
    setIsProductsLoading(true);
    setProductsError(null);

    try {
      const response = await fetch(`${apiBaseUrl}/api/products`);
      if (!response.ok) throw new Error(`${t('api_error')}: ${response.status}`);

      const data = (await response.json()) as Product[];
      setProducts(data);
      setProductsLoaded(true);
    } catch (error) {
      setProductsError(error instanceof Error ? error.message : t('unknown_error'));
    } finally {
      setIsProductsLoading(false);
    }
  };

  const openMouldStatusModal = (mould: Mould) => {
    setSelectedMould(mould);
    setNewMouldStatus(mould.status);
    setMouldProblemDescription(mould.problem_description || '');
    setShowMouldModal(true);
  };

  const updateMouldStatus = async () => {
    if (!selectedMould) return;
    setIsMouldUpdating(true);
    try {
      const response = await fetch(`${apiBaseUrl}/api/moulds/${selectedMould.id}/status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          status: newMouldStatus,
          problem_description: mouldProblemDescription,
        }),
      });
      if (!response.ok) throw new Error(`${t('error')}: ${response.status}`);
      const updated = (await response.json()) as Mould;
      setMoulds(moulds.map(m => m.id === updated.id ? updated : m));
      setShowMouldModal(false);
      Alert.alert(t('success'), t('mould_status_updated'));
    } catch (error) {
      Alert.alert(t('error'), error instanceof Error ? error.message : t('unknown_error'));
    } finally {
      setIsMouldUpdating(false);
    }
  };

  const getMouldStatusColor = (status: string) => {
    switch (status) {
      case 'active':       return '#22c55e';
      case 'standby':      return '#999999';
      case 'warning':      return '#f59e0b';
      case 'needs_repair': return '#ef4444';
      case 'under_repair': return '#3b82f6';
      default:             return '#999999';
    }
  };

  const getMouldStatusLabel = (status: string) => {
    switch (status) {
      case 'active':       return t('mould_status_active');
      case 'standby':      return t('mould_status_standby');
      case 'warning':      return t('mould_status_warning');
      case 'needs_repair': return t('mould_status_needs_repair');
      case 'under_repair': return t('mould_status_under_repair');
      default:             return status;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active': return '#22c55e';
      case 'stopped': return '#999999';
      case 'warning': return '#f59e0b';
      case 'broken': return '#ef4444';
      default: return '#999999';
    }
  };

  const getStatusLabel = (status: string) => {
    switch (status) {
      case 'active': return t('status_active');
      case 'stopped': return t('status_stopped');
      case 'warning': return t('status_warning');
      case 'broken': return t('status_broken');
      default: return status;
    }
  };

  const getOrderStatusLabel = (status: string) => {
    switch (status) {
      case 'in_progress':
        return t('order_status_in_progress');
      case 'paused':
        return t('order_status_paused');
      case 'completed':
        return t('order_status_completed');
      case 'interrupted':
        return t('order_status_interrupted');
      case 'waiting':
        return t('order_status_waiting');
      default:
        return status;
    }
  };

  const openMachineOrdersModal = (machine: DashboardMachine) => {
    setMachineOrdersMachine(machine);
    setShowMachineOrdersModal(true);
  };

  const getMachineOrders = (machineId: number) => {
    return orders
      .filter((order) => order.machine_id === machineId)
      .sort((a, b) => a.order_number.localeCompare(b.order_number));
  };

  const activateOrder = async (order: Order) => {
    setActivatingOrderId(order.id);
    try {
      const response = await fetch(`${apiBaseUrl}/api/orders/${order.id}/status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          status: 'in_progress',
        }),
      });

      if (!response.ok) throw new Error(`${t('error')}: ${response.status}`);

      const updated = (await response.json()) as Order;
      setOrders((current) => current.map((item) => (item.id === updated.id ? updated : item)));
      setShowMachineOrdersModal(false);
      Alert.alert(t('success'), t('order_status_updated'));

      if (!hasProtocolData(updated)) {
        openProtocolModal(updated);
      }
    } catch (error) {
      Alert.alert(t('error'), error instanceof Error ? error.message : t('unknown_error'));
    } finally {
      setActivatingOrderId(null);
    }
  };

  const switchLanguage = (lang: string) => {
    i18n.changeLanguage(lang);
  };

  const Navbar = () => (
    <View style={styles.navbar}>
      <View style={styles.navTitleGroup}>
        <Text style={styles.navTitle}>{t('app_title')}</Text>
        <Text style={styles.navSubtitle}>{t('dashboard_title')}</Text>
      </View>

      <View style={styles.navRightGroup}>
        <View style={styles.navLanguageGroup}>
          <Pressable
            style={[
              styles.langButton,
              i18n.language === 'hu' && styles.langButtonActive,
            ]}
            onPress={() => switchLanguage('hu')}
          >
            <Text style={styles.langButtonText}>🇭🇺</Text>
          </Pressable>
          <Pressable
            style={[
              styles.langButton,
              i18n.language === 'de' && styles.langButtonActive,
            ]}
            onPress={() => switchLanguage('de')}
          >
            <Text style={styles.langButtonText}>🇩🇪</Text>
          </Pressable>
        </View>

        <Pressable style={styles.menuButton} onPress={() => setShowMenu(true)}>
          <Text style={styles.menuButtonText}>☰</Text>
        </Pressable>
      </View>
    </View>
  );

  const HomeScreen = () => (
    <ScrollView contentContainerStyle={styles.homeContainer}>
      <Text style={styles.homeTitle}>{t('dashboard_title')}</Text>
      <Text style={styles.homeSubtitle}>{t('dashboard_subtitle')}</Text>

      <View style={styles.homeInfo}>
        <Text style={styles.homeInfoTitle}>Papirus</Text>
        <Text style={styles.homeInfoText}>
          Injekciós fröccsöntési gyárak intelligens kezelésére kifejlesztett integrált vállalatirányítási rendszer.
        </Text>
      </View>
    </ScrollView>
  );

  const MouldsScreen = () => (
    <View style={styles.container}>
      {isMouldsLoading && !mouldsLoaded && <ActivityIndicator size="large" color="#0f766e" />}
      {mouldsError ? <Text style={styles.errorText}>{mouldsError}</Text> : null}
      {mouldsLoaded && !isMouldsLoading && moulds.length === 0 ? (
        <Text style={styles.emptyText}>{t('no_moulds')}</Text>
      ) : null}
      <FlatList
        data={moulds}
        keyExtractor={(item) => item.id.toString()}
        renderItem={({ item }) => (
          <View style={styles.machineCard}>
            <View style={styles.machineHeader}>
              <Text style={styles.machineName}>{item.name}</Text>
              <View style={[styles.statusBadge, { backgroundColor: getMouldStatusColor(item.status) }]}>
                <Text style={styles.statusBadgeText}>{getMouldStatusLabel(item.status)}</Text>
              </View>
            </View>
            {item.product && (
              <Text style={styles.machineMeta}>
                {t('product')}: {item.product.name}
              </Text>
            )}
            <Text style={styles.machineMeta}>
              {t('mould_number')}: {item.number}
            </Text>
            <Text style={styles.machineMeta}>
              {t('cavity_count')}: {item.cavity_count ?? '-'}
            </Text>
            <Text style={styles.machineMeta}>
              {t('opening')}: {item.open_mm ?? '-'} {t('mm')}
            </Text>
            {item.problem_description && (
              <Text style={styles.problemText}>
                {t('problem')}: {item.problem_description}
              </Text>
            )}
            <View style={styles.buttonGroup}>
              <Pressable
                style={[styles.actionButton, { backgroundColor: '#0f766e' }]}
                onPress={() => openMouldStatusModal(item)}
              >
                <Text style={styles.actionButtonText}>{t('change_status')}</Text>
              </Pressable>
            </View>
          </View>
        )}
        contentContainerStyle={styles.listContent}
      />
    </View>
  );

  const renderMachinesScreen = () => (
    <View style={styles.container}>
      {(isLoading || isOrdersLoading) && !hasLoaded && !ordersLoaded && <ActivityIndicator size="large" color="#0f766e" />}

      {errorMessage || ordersError ? <Text style={styles.errorText}>{errorMessage || ordersError}</Text> : null}

      {hasLoaded && ordersLoaded && !isLoading && !isOrdersLoading && machines.length === 0 ? (
        <Text style={styles.emptyText}>{t('no_machines')}</Text>
      ) : null}

      <FlatList
        data={dashboardMachines}
        keyExtractor={(item) => item.id.toString()}
        renderItem={({ item }) => (
          <View style={styles.machineCard}>
            <View style={styles.machineHeader}>
              <View style={styles.machineHeaderLeft}>
                <Text style={styles.machineName}>{item.name}</Text>
                <Text style={styles.machineMeta}>
                  {t('machine_type')}: {item.type}
                </Text>
              </View>
              <View style={styles.machineHeaderRight}>
                <View style={[styles.statusBadge, { backgroundColor: getDashboardStatusColor(Boolean(item.currentOrder)) }]}>
                  <Text style={styles.statusBadgeText}>{getDashboardStatusLabel(Boolean(item.currentOrder))}</Text>
                </View>
                {item.currentOrder && (
                  <Pressable style={styles.issueButton} onPress={() => openIssueModal(item, 'machine')}>
                    <Text style={styles.issueButtonText}>⚠️</Text>
                  </Pressable>
                )}
              </View>
            </View>
            <Text style={styles.machineMeta}>
              {t('opening')}: {item.open_mm ?? '-'} {t('mm')}
            </Text>
            <Text style={styles.machineMeta}>
              {t('screw_diameter')}: {item.screw_diameter_cm ?? '-'} {t('cm')}
            </Text>
            {item.currentOrder ? (
              <View style={styles.currentOrderBox}>
                <Text style={styles.currentOrderTitle}>{t('current_order')}</Text>
                <Text style={styles.machineMeta}>
                  {t('order_number')}: {item.currentOrder.order_number}
                </Text>
                <Text style={styles.machineMeta}>
                  {t('total_quantity')}: {item.currentOrder.quantity}
                </Text>
                <Text style={styles.machineMeta}>
                  {t('started_at')}: {formatEta(item.currentOrder.started_at ? new Date(item.currentOrder.started_at) : null)}
                </Text>

                {(() => {
                  const draft = getProductionDraft(item.currentOrder);
                  const tracking = getOrderTracking(item.currentOrder, draft);

                  return (
                    <>
                      <View style={styles.productionInputsRow}>
                        <View style={styles.productionInputBlock}>
                          <Text style={styles.productionLabel}>{t('current_quantity')}</Text>
                          <TextInput
                            style={styles.productionInput}
                            keyboardType="numeric"
                            value={draft.current_quantity}
                            onChangeText={(value) => updateProductionDraft(item.currentOrder.id, 'current_quantity', value)}
                          />
                        </View>
                      </View>

                      <Pressable
                        style={[styles.actionButton, styles.productionSaveButton]}
                        onPress={() => saveProductionData(item.currentOrder!)}
                      >
                        <Text style={styles.actionButtonText}>{t('save_production_data')}</Text>
                      </Pressable>

                      <Pressable
                        style={[styles.actionButton, styles.protocolButton]}
                        onPress={() => openProtocolModal(item.currentOrder!)}
                      >
                        <Text style={styles.actionButtonText}>{t('production_protocol')}</Text>
                      </Pressable>

                      <Text style={styles.productionLabel}>{t('order_status_actions')}</Text>
                      <View style={styles.orderStatusButtonsRow}>
                        <Pressable
                          style={[styles.orderStatusButton, styles.orderPausedButton]}
                          onPress={() => updateOrderStatus(item.currentOrder!, 'paused')}
                        >
                          <Text style={styles.actionButtonText}>{t('order_status_paused')}</Text>
                        </Pressable>
                        <Pressable
                          style={[styles.orderStatusButton, styles.orderCompletedButton]}
                          onPress={() => openFinalizeModal(item.currentOrder!, 'completed')}
                        >
                          <Text style={styles.actionButtonText}>{t('order_status_completed')}</Text>
                        </Pressable>
                        <Pressable
                          style={[styles.orderStatusButton, styles.orderInterruptedButton]}
                          onPress={() => openFinalizeModal(item.currentOrder!, 'interrupted')}
                        >
                          <Text style={styles.actionButtonText}>{t('order_status_interrupted')}</Text>
                        </Pressable>
                      </View>

                      <View style={styles.progressGroup}>
                        <Text style={styles.progressLabel}>{t('theoretical_progress')} + {t('actual_progress')}</Text>
                        <View style={styles.progressBarOuter}>
                          <View
                            style={[
                              styles.progressBarInner,
                              styles.progressBarTheoretical,
                              { width: percentWidth(tracking.theoreticalProgress) },
                            ]}
                          />
                          <View
                            style={[
                              styles.progressBarInner,
                              styles.progressBarActual,
                              { width: percentWidth(tracking.actualProgress) },
                            ]}
                          />
                        </View>
                        <View style={styles.progressLegendRow}>
                          <Text style={styles.progressLegendItem}>
                            <Text style={styles.progressLegendSwatchTheoretical}>■</Text> {t('theoretical_progress')}: {Math.round(tracking.theoreticalProgress * 100)}%
                          </Text>
                          <Text style={styles.progressLegendItem}>
                            <Text style={styles.progressLegendSwatchActual}>■</Text> {t('actual_progress')}: {Math.round(tracking.actualProgress * 100)}%
                          </Text>
                        </View>

                        <Text style={styles.delayText}>
                          {t('delay')}: {formatDuration(tracking.delaySeconds)}
                        </Text>

                        <Text style={styles.machineMeta}>
                          {t('theoretical_eta')}: {formatEta(tracking.theoreticalEta)}
                        </Text>
                        <Text style={styles.machineMeta}>
                          {t('actual_eta')}: {formatEta(tracking.actualEta)}
                        </Text>
                      </View>
                    </>
                  );
                })()}
              </View>
            ) : (
              <Text style={styles.inactiveText}>{t('no_active_order')}</Text>
            )}
            {item.problem_description && (
              <Text style={styles.problemText}>
                {t('problem')}: {item.problem_description}
              </Text>
            )}

            <Pressable
              style={[styles.actionButton, styles.moreOrdersButton]}
              onPress={() => openMachineOrdersModal(item)}
            >
              <Text style={styles.actionButtonText}>{t('more_orders')}</Text>
            </Pressable>
          </View>
        )}
        contentContainerStyle={styles.listContent}
      />
    </View>
  );

  const renderProductsScreen = () => (
    <View style={styles.container}>
      {isProductsLoading && !productsLoaded && <ActivityIndicator size="large" color="#0f766e" />}

      {productsError ? <Text style={styles.errorText}>{productsError}</Text> : null}

      {productsLoaded && !isProductsLoading && products.length === 0 ? (
        <Text style={styles.emptyText}>{t('no_products')}</Text>
      ) : null}

      <FlatList
        data={products}
        keyExtractor={(item) => item.id.toString()}
        renderItem={({ item }) => (
          <View style={styles.machineCard}>
            <View style={styles.machineHeader}>
              <Text style={styles.machineName}>{item.name}</Text>
              <View style={[styles.statusBadge, { backgroundColor: item.is_active ? '#22c55e' : '#999999' }]}>
                <Text style={styles.statusBadgeText}>{item.is_active ? t('status_active') : t('status_inactive')}</Text>
              </View>
            </View>

            <Text style={styles.machineMeta}>
              {t('product_code')}: {item.code}
            </Text>

            <Text style={styles.machineMeta}>
              {t('material')}: {item.material ? `${item.material.name} (${item.material.code})` : '-'}
            </Text>

            <Text style={styles.machineMeta}>
              {t('color')}: {item.color ? `${item.color.name} (${item.color.code})` : '-'}
            </Text>

            <Text style={styles.machineMeta}>
              {t('additives')}: {item.additives && item.additives.length > 0
                ? item.additives.map(a => `${a.name} (${a.code})`).join(', ')
                : '-'}
            </Text>

            <Text style={styles.machineMeta}>
              {t('compatible_moulds')}: {item.moulds && item.moulds.length > 0
                ? item.moulds.map(m => `${m.name} (${m.number})`).join(', ')
                : '-'}
            </Text>
          </View>
        )}
        contentContainerStyle={styles.listContent}
      />
    </View>
  );

  return (
    <SafeAreaView style={styles.safeArea}>
      <Navbar />

      {currentScreen === 'machines' && renderMachinesScreen()}
      {currentScreen === 'moulds' && <MouldsScreen />}
      {currentScreen === 'products' && renderProductsScreen()}

      <Modal visible={showMenu} animationType="fade" transparent={true}>
        <Pressable style={styles.menuOverlay} onPress={() => setShowMenu(false)}>
          <View style={styles.menuDropdown}>
            <Pressable
              style={styles.menuItem}
              onPress={() => {
                setShowMenu(false);
                setCurrentScreen('machines');
                loadMachines();
                loadOrders();
              }}
            >
              <Text style={styles.menuItemText}>{t('machines_button_label')}</Text>
            </Pressable>
            <Pressable
              style={styles.menuItem}
              onPress={() => {
                setShowMenu(false);
                setCurrentScreen('moulds');
                loadMoulds();
              }}
            >
              <Text style={styles.menuItemText}>{t('moulds_button_label')}</Text>
            </Pressable>
            <Pressable
              style={styles.menuItem}
              onPress={() => {
                setShowMenu(false);
                setCurrentScreen('products');
                loadProducts();
              }}
            >
              <Text style={styles.menuItemText}>{t('products_button_label')}</Text>
            </Pressable>
          </View>
        </Pressable>
      </Modal>

      <Modal visible={showModal} animationType="slide" transparent={true}>
        <SafeAreaView style={styles.modalContainer}>
          <ScrollView style={styles.modalContent}>
            <Text style={styles.modalTitle}>{t('issue_report_title')}</Text>
            {selectedMachine && (
              <>
                <Text style={styles.modalMachineName}>{selectedMachine.name}</Text>

                <Text style={styles.modalLabel}>{t('issue_target')}:</Text>
                <View style={styles.targetButtonGroup}>
                  <Pressable
                    style={[styles.targetButton, issueTarget === 'machine' && styles.targetButtonSelected]}
                    onPress={() => openIssueModal(selectedMachine as DashboardMachine, 'machine')}
                  >
                    <Text style={styles.targetButtonText}>{t('machine')}</Text>
                  </Pressable>
                  {selectedMachine.currentOrder?.mould && (
                    <Pressable
                      style={[styles.targetButton, issueTarget === 'mould' && styles.targetButtonSelected]}
                      onPress={() => openIssueModal(selectedMachine as DashboardMachine, 'mould')}
                    >
                      <Text style={styles.targetButtonText}>{t('mould')}</Text>
                    </Pressable>
                  )}
                  {selectedMachine.currentOrder?.product && (
                    <Pressable
                      style={[styles.targetButton, issueTarget === 'product' && styles.targetButtonSelected]}
                      onPress={() => openIssueModal(selectedMachine as DashboardMachine, 'product')}
                    >
                      <Text style={styles.targetButtonText}>{t('product')}</Text>
                    </Pressable>
                  )}
                </View>

                <Text style={styles.modalLabel}>{t('new_status')}:</Text>
                {getIssueStatuses(issueTarget).map((status) => (
                  <Pressable
                    key={status}
                    style={[
                      styles.statusOption,
                      newStatus === status && styles.statusOptionSelected,
                    ]}
                    onPress={() => setNewStatus(status)}
                  >
                    <View
                      style={[
                        styles.statusOptionDot,
                        { backgroundColor: getIssueStatusColor(issueTarget, status) },
                      ]}
                    />
                    <Text style={styles.statusOptionText}>{getIssueStatusLabel(issueTarget, status)}</Text>
                  </Pressable>
                ))}

                {requiresProblemDescription(issueTarget, newStatus) && (
                  <>
                    <Text style={styles.modalLabel}>{t('problem_description')}:</Text>
                    <TextInput
                      style={styles.problemInput}
                      placeholder={t('problem_description_placeholder')}
                      value={problemDescription}
                      onChangeText={setProblemDescription}
                      multiline={true}
                      numberOfLines={4}
                    />
                  </>
                )}

                <View style={styles.modalButtonGroup}>
                  <Pressable
                    style={[styles.modalButton, { backgroundColor: '#ef4444' }]}
                    onPress={() => setShowModal(false)}
                    disabled={isUpdating}
                  >
                    <Text style={styles.modalButtonText}>{t('cancel')}</Text>
                  </Pressable>
                  <Pressable
                    style={[styles.modalButton, { backgroundColor: '#22c55e' }]}
                    onPress={updateMachineStatus}
                    disabled={isUpdating}
                  >
                    <Text style={styles.modalButtonText}>
                      {isUpdating ? t('updating') : t('save')}
                    </Text>
                  </Pressable>
                </View>
              </>
            )}
          </ScrollView>
        </SafeAreaView>
      </Modal>

      <Modal visible={showMouldModal} animationType="slide" transparent={true}>
        <SafeAreaView style={styles.modalContainer}>
          <ScrollView style={styles.modalContent}>
            <Text style={styles.modalTitle}>{t('change_status')}</Text>
            {selectedMould && (
              <>
                <Text style={styles.modalMachineName}>{selectedMould.name}</Text>
                <Text style={styles.modalLabel}>{t('new_status')}:</Text>
                {(['active', 'standby', 'warning', 'needs_repair', 'under_repair'] as const).map((status) => (
                  <Pressable
                    key={status}
                    style={[
                      styles.statusOption,
                      newMouldStatus === status && styles.statusOptionSelected,
                    ]}
                    onPress={() => setNewMouldStatus(status)}
                  >
                    <View style={[styles.statusOptionDot, { backgroundColor: getMouldStatusColor(status) }]} />
                    <Text style={styles.statusOptionText}>{getMouldStatusLabel(status)}</Text>
                  </Pressable>
                ))}

                {['warning', 'needs_repair'].includes(newMouldStatus) && (
                  <>
                    <Text style={styles.modalLabel}>{t('problem_description')}:</Text>
                    <TextInput
                      style={styles.problemInput}
                      placeholder={t('problem_description_placeholder')}
                      value={mouldProblemDescription}
                      onChangeText={setMouldProblemDescription}
                      multiline={true}
                      numberOfLines={4}
                    />
                  </>
                )}
                <View style={styles.modalButtonGroup}>
                  <Pressable
                    style={[styles.modalButton, { backgroundColor: '#ef4444' }]}
                    onPress={() => setShowMouldModal(false)}
                    disabled={isMouldUpdating}
                  >
                    <Text style={styles.modalButtonText}>{t('cancel')}</Text>
                  </Pressable>
                  <Pressable
                    style={[styles.modalButton, { backgroundColor: '#22c55e' }]}
                    onPress={updateMouldStatus}
                    disabled={isMouldUpdating}
                  >
                    <Text style={styles.modalButtonText}>
                      {isMouldUpdating ? t('updating') : t('update')}
                    </Text>
                  </Pressable>
                </View>
              </>
            )}
          </ScrollView>
        </SafeAreaView>
      </Modal>

      <Modal visible={showProtocolModal} animationType="slide" transparent={true}>
        <SafeAreaView style={styles.modalContainer}>
          <ScrollView style={styles.modalContent}>
            <Text style={styles.modalTitle}>{t('production_protocol')}</Text>

            {protocolDraft && (
              <>
                <Text style={styles.modalLabel}>{t('machine_name')}</Text>
                <TextInput
                  style={styles.productionInput}
                  value={protocolDraft.machine_name}
                  onChangeText={(value) => setProtocolDraft((current) => (current ? { ...current, machine_name: value } : current))}
                />

                <Text style={styles.modalLabel}>{t('part_code')}</Text>
                <TextInput
                  style={styles.productionInput}
                  value={protocolDraft.part_code}
                  onChangeText={(value) => setProtocolDraft((current) => (current ? { ...current, part_code: value } : current))}
                />

                <Text style={styles.modalLabel}>{t('material_code')}</Text>
                <TextInput
                  style={styles.productionInput}
                  value={protocolDraft.material_code}
                  onChangeText={(value) => setProtocolDraft((current) => (current ? { ...current, material_code: value } : current))}
                />

                <Text style={styles.modalLabel}>{t('color_code_optional')}</Text>
                <TextInput
                  style={styles.productionInput}
                  value={protocolDraft.color_code}
                  onChangeText={(value) => setProtocolDraft((current) => (current ? { ...current, color_code: value } : current))}
                />

                <Text style={styles.modalLabel}>{t('color_percentage_optional')}</Text>
                <TextInput
                  style={styles.productionInput}
                  keyboardType="numeric"
                  value={protocolDraft.color_percentage}
                  onChangeText={(value) => setProtocolDraft((current) => (current ? { ...current, color_percentage: value } : current))}
                />

                <Text style={styles.modalLabel}>{t('additives_codes_percentages_optional')}</Text>
                <TextInput
                  style={styles.problemInput}
                  value={protocolDraft.additives}
                  onChangeText={(value) => setProtocolDraft((current) => (current ? { ...current, additives: value } : current))}
                  multiline={true}
                  numberOfLines={4}
                />

                <Text style={styles.modalLabel}>{t('cycle_time_seconds')}</Text>
                <TextInput
                  style={styles.productionInput}
                  keyboardType="numeric"
                  value={protocolDraft.cycle_time}
                  onChangeText={(value) => setProtocolDraft((current) => (current ? { ...current, cycle_time: value } : current))}
                />

                <Text style={styles.modalLabel}>{t('total_quantity')}</Text>
                <TextInput
                  style={styles.productionInput}
                  keyboardType="numeric"
                  value={protocolDraft.quantity}
                  onChangeText={(value) => setProtocolDraft((current) => (current ? { ...current, quantity: value } : current))}
                />

                <Text style={styles.modalLabel}>{t('gross_weight_optional')}</Text>
                <TextInput
                  style={styles.productionInput}
                  keyboardType="numeric"
                  value={protocolDraft.gross_weight}
                  onChangeText={(value) => setProtocolDraft((current) => (current ? { ...current, gross_weight: value } : current))}
                />

                <Text style={styles.modalLabel}>{t('net_weight')}</Text>
                <TextInput
                  style={styles.productionInput}
                  keyboardType="numeric"
                  value={protocolDraft.net_weight}
                  onChangeText={(value) => setProtocolDraft((current) => (current ? { ...current, net_weight: value } : current))}
                />

                <View style={styles.modalButtonGroup}>
                  <Pressable
                    style={[styles.modalButton, { backgroundColor: '#ef4444' }]}
                    onPress={() => setShowProtocolModal(false)}
                    disabled={isProtocolSaving}
                  >
                    <Text style={styles.modalButtonText}>{t('cancel')}</Text>
                  </Pressable>
                  <Pressable
                    style={[styles.modalButton, { backgroundColor: '#22c55e' }]}
                    onPress={saveProductionProtocol}
                    disabled={isProtocolSaving}
                  >
                    <Text style={styles.modalButtonText}>
                      {isProtocolSaving ? t('updating') : t('save')}
                    </Text>
                  </Pressable>
                </View>
              </>
            )}
          </ScrollView>
        </SafeAreaView>
      </Modal>

      <Modal visible={showMachineOrdersModal} animationType="slide" transparent={true}>
        <SafeAreaView style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>{t('more_orders')}</Text>
            <Text style={styles.modalMachineName}>{machineOrdersMachine?.name ?? ''}</Text>

            {machineOrdersMachine && getMachineOrders(machineOrdersMachine.id).length === 0 ? (
              <Text style={styles.emptyText}>{t('no_orders_for_machine')}</Text>
            ) : (
              <ScrollView>
                {machineOrdersMachine && getMachineOrders(machineOrdersMachine.id).map((order) => (
                  <View key={order.id} style={styles.machineOrderListItem}>
                    <Text style={styles.machineName}>{order.order_number}</Text>
                    <Text style={styles.machineMeta}>{t('status')}: {getOrderStatusLabel(order.status)}</Text>
                    <Text style={styles.machineMeta}>{t('started_at')}: {formatEta(order.started_at ? new Date(order.started_at) : null)}</Text>
                    <Text style={styles.machineMeta}>{t('ended_at')}: {formatEta(order.ended_at ? new Date(order.ended_at) : null)}</Text>
                    <Text style={styles.machineMeta}>{t('total_quantity')}: {order.quantity}</Text>

                    {order.status !== 'in_progress' ? (
                      <Pressable
                        style={[styles.actionButton, styles.orderActivateButton]}
                        onPress={() => activateOrder(order)}
                        disabled={activatingOrderId === order.id}
                      >
                        <Text style={styles.actionButtonText}>
                          {activatingOrderId === order.id ? t('updating') : t('activate_order')}
                        </Text>
                      </Pressable>
                    ) : (
                      <View style={[styles.actionButton, styles.orderActiveBadge]}>
                        <Text style={styles.actionButtonText}>{t('order_status_in_progress')}</Text>
                      </View>
                    )}
                  </View>
                ))}
              </ScrollView>
            )}

            <Pressable
              style={[styles.modalButton, { backgroundColor: '#ef4444', marginTop: 12 }]}
              onPress={() => setShowMachineOrdersModal(false)}
            >
              <Text style={styles.modalButtonText}>{t('cancel')}</Text>
            </Pressable>
          </View>
        </SafeAreaView>
      </Modal>

      <Modal visible={showFinalizeModal} animationType="slide" transparent={true}>
        <SafeAreaView style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>
              {finalizeStatus === 'completed' ? t('order_status_completed') : t('order_status_interrupted')}
            </Text>
            <Text style={styles.modalMachineName}>{t('finalize_order_quantities')}</Text>

            <Text style={styles.modalLabel}>{t('good_quantity')}</Text>
            <TextInput
              style={styles.productionInput}
              keyboardType="numeric"
              value={finalizeGoodQuantity}
              onChangeText={setFinalizeGoodQuantity}
            />

            <Text style={styles.modalLabel}>{t('bad_quantity')}</Text>
            <TextInput
              style={styles.productionInput}
              keyboardType="numeric"
              value={finalizeBadQuantity}
              onChangeText={setFinalizeBadQuantity}
            />

            <View style={styles.modalButtonGroup}>
              <Pressable
                style={[styles.modalButton, { backgroundColor: '#ef4444' }]}
                onPress={() => setShowFinalizeModal(false)}
              >
                <Text style={styles.modalButtonText}>{t('cancel')}</Text>
              </Pressable>
              <Pressable
                style={[styles.modalButton, { backgroundColor: '#22c55e' }]}
                onPress={submitFinalizeStatus}
              >
                <Text style={styles.modalButtonText}>{t('save')}</Text>
              </Pressable>
            </View>
          </View>
        </SafeAreaView>
      </Modal>

      <StatusBar style="auto" />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#f1f5f9',
  },
  navbar: {
    backgroundColor: '#0f766e',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 2,
    borderBottomColor: '#0d5f59',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.15,
    shadowRadius: 3,
    elevation: 5,
  },
  navButton: {
    paddingVertical: 8,
    paddingHorizontal: 16,
    borderRadius: 8,
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
  },
  navButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '600',
  },
  navLanguageGroup: {
    flexDirection: 'row',
    gap: 8,
  },
  navTitleGroup: {
    flexDirection: 'column',
    flex: 1,
    marginRight: 12,
  },
  navRightGroup: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  navTitle: {
    color: '#ffffff',
    fontSize: 18,
    fontWeight: '800',
  },
  navSubtitle: {
    color: 'rgba(255,255,255,0.85)',
    fontSize: 12,
    fontWeight: '500',
  },
  menuButton: {
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 8,
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
  },
  menuButtonText: {
    color: '#ffffff',
    fontSize: 18,
    fontWeight: '800',
  },
  menuOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.15)',
    alignItems: 'flex-end',
    justifyContent: 'flex-start',
    paddingTop: 72,
    paddingRight: 16,
  },
  menuDropdown: {
    backgroundColor: '#ffffff',
    borderRadius: 14,
    paddingVertical: 8,
    minWidth: 180,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 8,
  },
  menuItem: {
    paddingVertical: 12,
    paddingHorizontal: 16,
  },
  menuItemText: {
    fontSize: 15,
    color: '#0f172a',
    fontWeight: '600',
  },
  langButton: {
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 8,
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    borderWidth: 2,
    borderColor: 'transparent',
  },
  langButtonActive: {
    borderColor: '#ffffff',
    backgroundColor: 'rgba(255, 255, 255, 0.3)',
  },
  langButtonText: {
    fontSize: 18,
  },
  container: {
    flex: 1,
    paddingHorizontal: 16,
    paddingTop: 16,
  },
  homeContainer: {
    flexGrow: 1,
    paddingHorizontal: 16,
    paddingTop: 32,
    paddingBottom: 32,
    justifyContent: 'center',
    alignItems: 'center',
  },
  homeTitle: {
    fontSize: 36,
    fontWeight: '800',
    color: '#0f172a',
    marginBottom: 8,
    textAlign: 'center',
  },
  homeSubtitle: {
    fontSize: 16,
    color: '#64748b',
    marginBottom: 32,
    textAlign: 'center',
    fontWeight: '500',
  },
  homeButton: {
    backgroundColor: '#0f766e',
    borderRadius: 16,
    paddingVertical: 18,
    paddingHorizontal: 48,
    marginBottom: 32,
    shadowColor: '#0f766e',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 5,
  },
  homeButtonText: {
    color: '#ffffff',
    fontSize: 20,
    fontWeight: '700',
    textAlign: 'center',
  },
  homeInfo: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 20,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
    maxWidth: 400,
    width: '100%',
  },
  homeInfoTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#0f766e',
    marginBottom: 8,
  },
  homeInfoText: {
    fontSize: 14,
    color: '#475569',
    lineHeight: 22,
  },
  listContent: {
    paddingBottom: 24,
    gap: 12,
  },
  machineCard: {
    backgroundColor: '#ffffff',
    borderRadius: 14,
    padding: 14,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    marginBottom: 12,
  },
  machineHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  machineHeaderLeft: {
    flex: 1,
    paddingRight: 8,
  },
  machineHeaderRight: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  machineName: {
    fontSize: 17,
    fontWeight: '700',
    color: '#0f172a',
    flex: 1,
  },
  statusBadge: {
    marginLeft: 8,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  statusBadgeText: {
    color: '#ffffff',
    fontSize: 12,
    fontWeight: '600',
  },
  issueButton: {
    width: 36,
    height: 36,
    borderRadius: 18,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#fee2e2',
  },
  issueButtonText: {
    fontSize: 18,
  },
  currentOrderBox: {
    marginTop: 10,
    padding: 12,
    borderRadius: 12,
    backgroundColor: '#f8fafc',
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  currentOrderTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: '#0f766e',
    marginBottom: 6,
  },
  inactiveText: {
    marginTop: 10,
    color: '#64748b',
    fontStyle: 'italic',
  },
  machineMeta: {
    fontSize: 14,
    color: '#334155',
    marginVertical: 2,
  },
  problemText: {
    fontSize: 13,
    color: '#dc2626',
    fontWeight: '500',
    marginTop: 8,
    fontStyle: 'italic',
  },
  progressGroup: {
    marginTop: 14,
    padding: 14,
    borderRadius: 14,
    backgroundColor: '#f8fafc',
    borderWidth: 1,
    borderColor: '#cbd5e1',
  },
  progressLabel: {
    fontSize: 12,
    fontWeight: '700',
    color: '#334155',
    marginBottom: 6,
  },
  progressBarOuter: {
    position: 'relative',
    width: '100%',
    height: 16,
    backgroundColor: '#e2e8f0',
    borderRadius: 999,
    overflow: 'hidden',
    marginBottom: 4,
    borderWidth: 1,
    borderColor: '#cbd5e1',
  },
  progressBarInner: {
    position: 'absolute',
    left: 0,
    top: 0,
    height: '100%',
    borderRadius: 999,
    minWidth: 4,
  },
  progressBarTheoretical: {
    backgroundColor: '#ef4444',
    opacity: 0.7,
    zIndex: 1,
  },
  progressBarActual: {
    backgroundColor: '#2563eb',
    opacity: 0.95,
    zIndex: 2,
  },
  progressLegendRow: {
    marginTop: 8,
    marginBottom: 4,
  },
  progressLegendItem: {
    fontSize: 12,
    fontWeight: '600',
    color: '#0f172a',
    marginBottom: 2,
  },
  progressLegendSwatchTheoretical: {
    color: '#ef4444',
    fontWeight: '800',
  },
  progressLegendSwatchActual: {
    color: '#2563eb',
    fontWeight: '800',
  },
  delayText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#b91c1c',
    marginTop: 8,
  },
  productionInputsRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginTop: 10,
  },
  productionInputBlock: {
    flexGrow: 1,
    flexBasis: '30%',
    minWidth: 96,
  },
  productionLabel: {
    fontSize: 11,
    fontWeight: '700',
    color: '#475569',
    marginBottom: 4,
  },
  productionInput: {
    borderWidth: 1,
    borderColor: '#cbd5e1',
    borderRadius: 10,
    paddingHorizontal: 10,
    paddingVertical: 8,
    backgroundColor: '#ffffff',
    fontSize: 14,
    color: '#0f172a',
  },
  productionSaveButton: {
    marginTop: 12,
    flex: 0,
    alignSelf: 'flex-start',
    minWidth: 200,
    backgroundColor: '#0f766e',
  },
  protocolButton: {
    marginTop: 8,
    flex: 0,
    alignSelf: 'flex-start',
    minWidth: 200,
    backgroundColor: '#0369a1',
  },
  moreOrdersButton: {
    marginTop: 10,
    backgroundColor: '#475569',
  },
  orderStatusButtonsRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginTop: 8,
  },
  orderStatusButton: {
    flexGrow: 1,
    minWidth: 110,
    paddingVertical: 10,
    paddingHorizontal: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  orderPausedButton: {
    backgroundColor: '#334155',
  },
  orderCompletedButton: {
    backgroundColor: '#15803d',
  },
  orderInterruptedButton: {
    backgroundColor: '#b91c1c',
  },
  machineOrderListItem: {
    padding: 12,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 10,
    backgroundColor: '#f8fafc',
    marginBottom: 10,
  },
  orderActivateButton: {
    backgroundColor: '#0f766e',
    marginTop: 8,
  },
  orderActiveBadge: {
    backgroundColor: '#15803d',
    marginTop: 8,
  },
  buttonGroup: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 12,
  },
  actionButton: {
    flex: 1,
    paddingVertical: 10,
    paddingHorizontal: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  actionButtonText: {
    color: '#ffffff',
    fontSize: 13,
    fontWeight: '600',
  },
  errorText: {
    color: '#b91c1c',
    marginBottom: 10,
    fontWeight: '500',
  },
  emptyText: {
    color: '#334155',
    marginBottom: 10,
    textAlign: 'center',
  },
  modalContainer: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
  },
  modalContent: {
    backgroundColor: '#ffffff',
    margin: 16,
    borderRadius: 16,
    padding: 20,
    flex: 1,
  },
  modalTitle: {
    fontSize: 22,
    fontWeight: '700',
    color: '#0f172a',
    marginBottom: 4,
  },
  modalMachineName: {
    fontSize: 16,
    color: '#0f766e',
    fontWeight: '600',
    marginBottom: 16,
  },
  modalLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#334155',
    marginTop: 16,
    marginBottom: 8,
  },
  statusOption: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 12,
    marginBottom: 8,
    borderRadius: 8,
    borderWidth: 2,
    borderColor: '#e2e8f0',
  },
  statusOptionSelected: {
    borderColor: '#0f766e',
    backgroundColor: '#ecf9f8',
  },
  statusOptionDot: {
    width: 16,
    height: 16,
    borderRadius: 8,
    marginRight: 12,
  },
  statusOptionText: {
    fontSize: 15,
    color: '#0f172a',
    fontWeight: '500',
  },
  problemInput: {
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 8,
    padding: 12,
    fontSize: 14,
    color: '#0f172a',
    textAlignVertical: 'top',
    minHeight: 100,
  },
  modalButtonGroup: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 24,
    marginBottom: 24,
  },
  modalButton: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  modalButtonText: {
    color: '#ffffff',
    fontSize: 15,
    fontWeight: '600',
  },
  targetButtonGroup: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  targetButton: {
    paddingVertical: 10,
    paddingHorizontal: 14,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#cbd5e1',
    backgroundColor: '#ffffff',
  },
  targetButtonSelected: {
    backgroundColor: '#ecfdf5',
    borderColor: '#0f766e',
  },
  targetButtonText: {
    color: '#0f172a',
    fontSize: 13,
    fontWeight: '600',
    textTransform: 'capitalize',
  },
});
